<?php

namespace App\Repositories;
use App\Models\PhoneNumber;
use App\Models\Message;
use App\Models\OmniSetting;
use App\Models\User;
use Josantonius\MimeType\MimeType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Events\MessageReceived;

class WhatsAppRepository {

  /**
   * Token de verificación para el webhook
   *
   * @var string
  */
  protected $verificationToken = 'SumeriaWPApp';

  /**
   * Base URL de la API de Facebook
   *
   * @var string
  */
  protected $baseUrl = 'https://graph.facebook.com/v17.0';

  /**
   * Token de acceso a la API de Facebook
   *
   * @var string
  */
  protected $token;

  /**
   * Inicializa el controlador
  */
  public function __construct()
  {
    $this->token = OmniSetting::where('setting_name', 'metaAdminToken')->value('setting_value');
  }

  /**
   * Webhook encargado de recibir los mensajes enviados y recibidos por WhatsApp.
   *
   * @return void
  */
  public function webhook(): void {
    $hubChallenge = isset($_GET['hub_challenge']) ? $_GET['hub_challenge'] : '';

    $hubVerifyToken = isset($_GET['hub_verify_token']) ? $_GET['hub_verify_token'] : '';

    if ($this->verificationToken === $hubVerifyToken) {
      echo $hubChallenge;
      exit;
    }
  }

  /**
   * Procesa los mensjaes recibidos de WhatsApp.
   *
   * @return void
  */
  public function recibe(): void {
    try {
      $response = file_get_contents('php://input'); // Obtengo la respuesta

      if ($response == null) { // Si no hay respuesta registro el error
        Log::error('No se recibió respuesta');
      }

      Log::info('Respuesta: ' . $response); // Guardo la respuesta en un archivo de logs

      $decodedResponse = json_decode($response, true); // Decodifico la respuesta

      $messageType = $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['type']; // Obtengo el tipo de mensaje

      $receiverPhoneNumberId = $decodedResponse['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'] ?? null; // Obtengo el ID del número de teléfono receptor
      $senderPhoneNumberId = $decodedResponse['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id']; // Obtengo el ID del número de teléfono emisor

      $receiverPhone = PhoneNumber::firstOrCreate(['phone_id' => $receiverPhoneNumberId], [
          'phone_number' => $decodedResponse['entry'][0]['changes'][0]['value']['metadata']['display_phone_number'],
          'phone_number_owner' => $decodedResponse['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'],
      ]);

      $senderPhone = PhoneNumber::firstOrCreate(['phone_id' => $senderPhoneNumberId], [
        'phone_number_owner' => $decodedResponse['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'],
        'phone_number' => $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['from'],
      ]);

      // Dependiendo el tipo de mensaje ejecuto
      switch ($messageType) {
        case 'text':
          $this->processTextMessage($decodedResponse, $receiverPhone, $senderPhone);
          break;
        case 'image':
          $this->processImageMessage($decodedResponse, $receiverPhone, $senderPhone);
          break;
        case 'document':
          $this->processDocumentMessage($decodedResponse, $receiverPhone, $senderPhone);
          break;
        case 'audio':
          $this->processAudioMessage($decodedResponse, $receiverPhone, $senderPhone);
          break;
        case 'video':
          $this->processVideoMessage($decodedResponse, $receiverPhone, $senderPhone);
          break;
        case 'sticker':
          $this->processStickerMessage($decodedResponse, $receiverPhone, $senderPhone);
          break;
      }
    } catch (\Exception $e) {
      Log::error($e->getMessage());
    }
  }

  /**
   * Procesa los mensajes de texto recibidos de WhatsApp
   *
   * @param array $decodedResponse
   * @param PhoneNumber $receiverPhone
   * @param PhoneNumber $senderPhone
   * @return void
  */
  private function processTextMessage(array $decodedResponse, PhoneNumber $receiverPhone, PhoneNumber $senderPhone): void {
    $created = date("Y-m-d H:i:s", $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['timestamp']); // Obtengo la fecha de creación del mensaje

    $messageBody = $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']; // Obtengo el cuerpo del mensaje

    try {
      $message = Message::create([
        'from_phone_id' => $senderPhone->phone_id,
        'to_phone_id' => $receiverPhone->phone_id,
        'message_source' => 'WhatsApp',
        'message_type' => 'text',
        'message_text' => $messageBody,
        'created_at' => $created,
      ]);

      MessageReceived::dispatch($message, $senderPhone->phone_number_owner);
    } catch (\Exception $e) {
      Log::error($e->getMessage());
    }
  }

  /**
   * Obtiene la URL de descarga de un archivo multimedia de WhatsApp
   *
   * @param string $mediaId
   * @return string|null
  */
  private function getMediaUrl(string $mediaId): ?string
  {
    try {
      $response = Http::withToken($this->token)->get("{$this->baseUrl}/$mediaId"); // Realizo la petición a la API de Facebook

      if ($response->successful()) { // Si la petición fue exitosa
          $data = $response->json(); // Decodifico la respuesta
          return $data['url'] ?? null; // Retorno la URL del archivo multimedia
      }
    } catch (\Exception $e) {
        return null;
    }
  }

  /**
   * Obtiene la extensión de un archivo multimedia en funcion de su MIME Type.
   *
   * @param string $mimeType
   * @return string
  */
  private function getExtensionFromMimeType(string $mimeType): string {
    $mimetypeObject = new MimeType(); // Instancio el objeto MimeType
    $extension = $mimetypeObject->getExtension($mimeType); // Obtengo la extensión del archivo

    if ($extension == null) { // Si no se encuentra la extensión
      return 'unknown';
    } else { // Si se encuentra la extensión
      return '.' . $extension;
    }
  }

  /**
   * Descarga un archivo multimedia de WhatsApp
   *
   * @param string $mediaId
   * @param string $extension
   * @return string|null
  */
  public function downloadMedia($mediaId, $extension): ?string
  {
      $tempUrl = $this->getMediaUrl($mediaId);

      if ($tempUrl) {
          $ch = curl_init($tempUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HEADER, false);
          curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.64.1');
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_AUTOREFERER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
              "Authorization: Bearer " . $this->token
          ]);

          $data = curl_exec($ch);
          $error = curl_error($ch);
          curl_close($ch);

          if ($error) {
            return null;
          }

          if ($data) {
            $path = 'whatsapp_media/' . $mediaId . $extension;
            Storage::disk('public')->put($path, $data);
            $publicPath = 'storage/' . $path;
            return $publicPath;
          }
      }

      return null;
  }

  /**
   * Procesa los mensajes de imagenes recibidas de WhatsApp.
   *
   * @param array $decodedResponse El arreglo decodificado de la respuesta de WhatsApp.
   * @param PhoneNumber $senderPhone El PhoneNumber del remitente.
   * @param PhoneNumber $receiverPhone El PhoneNumber del receptor.
   * @return void
  */
  private function processImageMessage(array $decodedResponse, PhoneNumber $receiverPhone, PhoneNumber $senderPhone):void
  {
      $created = date("Y-m-d H:i:s", $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['timestamp']);

      try {
        $imageData = $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['image'];
        $imageId = $imageData['id'];
        $imageCaption = $imageData['caption'] ?? '';
        $imageMimeType = $imageData['mime_type'];

        $extension = $this->getExtensionFromMimeType($imageMimeType);
        $imageUrl = $this->downloadMedia($imageId, $extension);

        if ($imageUrl) {
            $message = Message::create([
                'from_phone_id' => $senderPhone->phone_id,
                'to_phone_id' => $receiverPhone->phone_id,
                'message_source' => 'whatsapp',
                'image_url' => $imageUrl,
                'message_text' => $imageCaption,
                'message_type' => 'image',
                'created_at' => $created,
            ]);

            MessageReceived::dispatch($message, $senderPhone->phone_number_owner);
        }
      } catch (\Exception $e) {
        Log::error($e->getMessage());
      }
  }

  /**
   * Procesa los mensajes de audios recibidos de WhatsApp.
   *
   * @param array $decodedResponse El arreglo decodificado de la respuesta de WhatsApp.
   * @param PhoneNumber $senderPhone El PhoneNumber del remitente.
   * @param PhoneNumber $receiverPhone El PhoneNumber del receptor.
   * @return void
  */
  private function processAudioMessage(array $decodedResponse, PhoneNumber $receiverPhone, PhoneNumber $senderPhone): void
  {
      $created = date("Y-m-d H:i:s", $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['timestamp']);

      try {
        $audioData = $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['audio'];
        $audioId = $audioData['id'];
        $audioMimeType = $audioData['mime_type'];

        $extension = $this->getExtensionFromMimeType($audioMimeType);
        $audioUrl = $this->downloadMedia($audioId, $extension);
        $audioCaption = $audioData['caption'] ?? '';

        if ($audioUrl) {
            $message = Message::create([
                'from_phone_id' => $senderPhone->phone_id,
                'to_phone_id' => $receiverPhone->phone_id,
                'message_text' => $audioCaption,
                'message_source' => 'whatsapp',
                'audio_url' => $audioUrl,
                'message_type' => 'audio',
                'created_at' => $created,
            ]);

            MessageReceived::dispatch($message, $senderPhone->phone_number_owner);
        }
      } catch (\Exception $e) {
        Log::error($e->getMessage());
      }
  }


  /**
   * Procesa los mensajes de documentos recibidos de WhatsApp.
   *
   * @param array $decoded_response El arreglo decodificado de la respuesta de WhatsApp.
   * @param PhoneNumber $senderPhone El PhoneNumber del remitente.
   * @param PhoneNumber $receiverPhone El PhoneNumber del receptor.
   * @return void
  */
  private function processDocumentMessage(array $decodedResponse, PhoneNumber $receiverPhone, PhoneNumber $senderPhone): void
  {
      $created = date("Y-m-d H:i:s", $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['timestamp']);

      try {
        $documentData = $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['document'];
        $documentId = $documentData['id'];
        $documentMimeType = $documentData['mime_type'];

        $extension = $this->getExtensionFromMimeType($documentMimeType);
        $documentUrl = $this->downloadMedia($documentId, $extension);
        $documentCaption = $documentData['caption'] ?? '';

        if ($documentUrl) {
            if ($documentMimeType == 'audio/mpeg') {
                $message = Message::create([
                    'from_phone_id' => $senderPhone->phone_id,
                    'to_phone_id' => $receiverPhone->phone_id,
                    'message_source' => 'whatsapp',
                    'audio_url' => $documentUrl,
                    'message_text' => $documentCaption,
                    'message_type' => 'audio',
                    'created_at' => $created,
                ]);

                MessageReceived::dispatch($message, $senderPhone->phone_number_owner);
            } else {
                $message = Message::create([
                    'from_phone_id' => $senderPhone->phone_id,
                    'to_phone_id' => $receiverPhone->phone_id,
                    'message_source' => 'whatsapp',
                    'document_url' => $documentUrl,
                    'message_text' => $documentCaption,
                    'message_type' => 'document',
                    'created_at' => $created,
                ]);

                MessageReceived::dispatch($message, $senderPhone->phone_number_owner);
            }
        }
      } catch (\Exception $e) {
        Log::error($e->getMessage());
      }
  }

  /**
   * Procesa los mensajes de videos recibidos de WhatsApp.
   *
   * @param array $decodedResponse El arreglo decodificado de la respuesta de WhatsApp.
   * @param PhoneNumber $senderPhone El PhoneNumber del remitente.
   * @param PhoneNumber $receiverPhone El PhoneNumber del receptor.
   * @return void
  */
  private function processVideoMessage(array $decodedResponse, PhoneNumber $receiverPhone, PhoneNumber $senderPhone): void
  {
      $created = date("Y-m-d H:i:s", $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['timestamp']);

      try {
        $videoData = $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['video'];
        $videoId = $videoData['id'];
        $videoMimeType = $videoData['mime_type'];

        $extension = $this->getExtensionFromMimeType($videoMimeType);
        $videoUrl = $this->downloadMedia($videoId, $extension);
        $videoCaption = $videoData['caption'] ?? '';

        if ($videoUrl) {
            $message = Message::create([
                'from_phone_id' => $senderPhone->phone_id,
                'to_phone_id' => $receiverPhone->phone_id,
                'message_source' => 'whatsapp',
                'video_url' => $videoUrl,
                'message_text' => $videoCaption,
                'message_type' => 'video',
                'created_at' => $created,
            ]);

            MessageReceived::dispatch($message, $senderPhone->phone_number_owner);
        }
      } catch (\Exception $e) {
        Log::error($e->getMessage());
      }
  }

  /**
   * Procesa los mensajes de stickers recibidos de WhatsApp.
   *
   * @param array $decodedResponse El arreglo decodificado de la respuesta de WhatsApp.
   * @param PhoneNumber $senderPhone El PhoneNumber del remitente.
   * @param PhoneNumber $receiverPhone El PhoneNumber del receptor.
   * @return void
  */
  private function processStickerMessage(array $decodedResponse, PhoneNumber $receiverPhone, PhoneNumber $senderPhone): void
  {
      $created = date("Y-m-d H:i:s", $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['timestamp']);

      try {
        $stickerData = $decodedResponse['entry'][0]['changes'][0]['value']['messages'][0]['sticker'];
        $stickerId = $stickerData['id'];
        $stickerMimeType = $stickerData['mime_type'];

        $extension = $this->getExtensionFromMimeType($stickerMimeType);
        $stickerUrl = $this->downloadMedia($stickerId, $extension);

        if ($stickerUrl) {
            $message = Message::create([
                'from_phone_id' => $senderPhone->phone_id,
                'to_phone_id' => $receiverPhone->phone_id,
                'message_source' => 'whatsapp',
                'sticker_url' => $stickerUrl,
                'message_type' => 'sticker',
                'created_at' => $created,
            ]);

            MessageReceived::dispatch($message, $senderPhone->phone_number_owner);
        }
      } catch (\Exception $e) {
        Log::error($e->getMessage());
      }
  }

  /**
   * Obtiene los mensajes entre el número de teléfono de la tienda del usuario y un número de teléfono de contacto.
   *
   * @param int $userId Identificador del usuario solicitante.
   * @param string $contactPhoneNumber Número de teléfono de contacto.
   * @return mixed
  */
  public function fetchMessagesForUserStore(int $userId, string $contactPhoneNumber): mixed
  {
      $user = User::find($userId);
      $userStore = $user->store;

      if (!$userStore || !$userStore->phoneNumber) {
          return ['error' => 'Número de teléfono no asociado.', 'status' => 404];
      }

      if (!$contactPhoneNumber || $contactPhoneNumber == $userStore->phoneNumber->phone_id) {
          return ['error' => 'Número de teléfono inválido.', 'status' => 400];
      }

      $messages = Message::where(function ($query) use ($userStore, $contactPhoneNumber) {
                                      $query->where('from_phone_id', $userStore->phoneNumber->phone_id)
                                            ->where('to_phone_id', $contactPhoneNumber);
                                  })
                                  ->orWhere(function ($query) use ($userStore, $contactPhoneNumber) {
                                      $query->where('from_phone_id', $contactPhoneNumber)
                                            ->where('to_phone_id', $userStore->phoneNumber->phone_id);
                                  })
                                  ->with(['sender', 'receiver'])
                                  ->orderBy('created_at', 'desc')
                                  ->get();

      return ['messages' => $messages, 'status' => 200];
  }
}
