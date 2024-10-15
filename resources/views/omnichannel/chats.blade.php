@extends('layouts/layoutMaster')

@section('title', 'Omnicanalidad')

@section('vendor-style')
  @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.scss')
@endsection

@section('page-style')
  @vite('resources/assets/vendor/scss/pages/app-chat.scss')
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js')
@endsection

@section('page-script')
  @vite('resources/assets/js/app-chat.js')
@endsection

@section('content')
<h4 class="mb-4 d-flex">
  <span class="text-muted fw-light">
  Omnicanalidad /</span>

  Chats de WhatsApp de {{ auth()->user()->store->phoneNumber->phone_number }}

  <div class="mute-button">
    <button class="btn btn-primary" id="mute-button" style="
      padding: 3px;
      padding-inline: 10px;
      margin-left: 10px;
      margin-top: -5px;
      border: none;
  "><i class="bx bx-volume-mute" style="margin-right: 5px;"></i><span>Silenciar</span></button>
  </div>
</h4>
<div class="app-chat overflow-hidden card">
  <div class="row g-0">
    <!-- Sidebar Left -->
    <div class="col app-chat-sidebar-left app-sidebar overflow-hidden" id="app-chat-sidebar-left">
      <div class="chat-sidebar-left-user sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap p-4 mt-2">
        <div class="avatar avatar-xl avatar-online">
          <img src="https://ui-avatars.com/api/?background=random&name={{ urlencode(auth()->user->name ?? 'NA') }}" alt="User Avatar" class="rounded-circle">
        </div>
      </div>
    </div>
    <!-- /Sidebar Left-->

    <!-- Chat & Contacts -->
    <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
      <div class="sidebar-header pt-3 px-3 mx-1">
        <div class="d-flex align-items-center me-3 me-lg-0">
          <div class="flex-shrink-0 avatar avatar-online me-2" data-bs-toggle="sidebar" data-overlay="app-overlay-ex" data-target="#app-chat-sidebar-left">
            <img src="https://ui-avatars.com/api/?background=random&name={{ urlencode(auth()->user()->name ?? 'NA') }}" alt="User Avatar" class="rounded-circle">
          </div>
          <div class="flex-grow-1 input-group input-group-merge rounded-pill ms-1">
            <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search fs-4"></i></span>
            <input type="text" class="form-control chat-search-input" placeholder="Buscar..." aria-label="Buscar..." aria-describedby="basic-addon-search31">
          </div>
        </div>
        <i class="bx bx-x cursor-pointer position-absolute top-0 end-0 mt-2 me-1 fs-4 d-lg-none d-block" data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
      </div>
      <hr class="container-m-nx mt-3 mb-0">
      <div class="sidebar-body">
        <!-- Chats -->
        <ul class="list-unstyled chat-contact-list pt-1" id="chat-list">
          <li class="chat-contact-list-item chat-contact-list-item-title chat-title">
            <h5 class="text-primary mb-0">Conversaciones</h5>
          </li>
          @forelse ($chats as $chat)
            <li class="chat-contact-list-item" data-phone-number-id="{{$chat->sender->phone_id == auth()->user()->store->phoneNumber->phone_id ? $chat->receiver->phone_id : $chat->sender->phone_id }}" data-contact-name="{{$chat->sender->phone_id == auth()->user()->store->phoneNumber->phone_id ? $chat->receiver->phone_number_owner : $chat->sender->phone_number_owner }}" data-message-created="{{ $chat->message_created }}">
              <a class="d-flex align-items-center">
                <div class="flex-shrink-0 avatar avatar-online">
                  <img src="https://ui-avatars.com/api/?background=random&name={{ urlencode($chat->sender->phone_id == auth()->user()->store->phoneNumber->phone_id ? $chat->receiver->phone_number_owner : $chat->sender->phone_number_owner ?? 'NA') }}" alt="Avatar" class="rounded-circle">
                </div>
                <div class="chat-contact-info flex-grow-1 ms-3">
                  @php
                    $messagePreview = '';
                    switch ($chat->message_type) {
                        case 'image':
                            $messagePreview = 'ðŸ“· ' . ($chat->message_text ?: 'Imagen');
                            break;
                        case 'audio':
                            $messagePreview = 'ðŸ”Š ' . ($chat->message_text ?: 'Audio');
                            break;
                        case 'document':
                            $messagePreview = 'ðŸ“„ ' . ($chat->message_text ?: 'Documento');
                            break;
                        case 'video':
                            $messagePreview = 'ðŸŽ¥ ' . ($chat->message_text ?: 'Video');
                            break;
                        case 'sticker':
                            $messagePreview = 'ðŸŒŸ ' . ($chat->message_text ?: 'Sticker');
                            break;
                        default:
                            $messagePreview = $chat->message_text;
                    }
                  @endphp
                  <h6 class="chat-contact-name text-truncate m-0">{{ $chat->sender->phone_id == auth()->user()->store->phoneNumber->phone_id ? $chat->receiver->phone_number_owner : ($chat->sender->phone_number_owner ?? 'Desconocido') }}</h6>
                  <p class="chat-contact-status text-truncate mb-0 text-muted">{{ $messagePreview }}</p>
                </div>
                <small class="text-muted mb-auto">{{ $chat->message_created->diffForHumans() }}</small>
              </a>
            </li>
          @empty
            <li class="chat-contact-list-item chat-list-item-0 chat-title chat-title-empty">
              <h6 class="text-muted mb-0">No se encontraron conversaciones</h6>
            </li>
          @endforelse
        </ul>


      </div>
    </div>
    <!-- /Chat contacts -->

    <!-- Chat History -->
    <div class="col app-chat-history">
      <div class="chat-history-wrapper">
        <div class="chat-history-header border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex overflow-hidden align-items-center">
              <i class="bx bx-menu bx-sm cursor-pointer d-lg-none d-block me-2" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts"></i>
              <div class="flex-shrink-0 avatar avatar-header">
                <img src="https://ui-avatars.com/api/?background=random&name=!" alt="Avatar" class="rounded-circle" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-right">
              </div>
              <!-- Chat Header -->
              <div class="chat-contact-info chat-header-info flex-grow-1 ms-3">
                <h6 class="m-0">SeleccionÃ¡ un chat para comenzar</h6>
              </div>
              <!-- Chat Header -->
            </div>
          </div>
        </div>
        <div class="chat-history-body">
          <ul class="list-unstyled chat-history mb-0">
            <li class="chat-message chat-message-right">
              <div class="d-flex overflow-hidden">
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">EmpezÃ¡ a chatear seleccionando un chat en el listado de la izquierda !</p>
                  </div>
                  <div class="text-end text-muted mt-1">
                    <i class='bx bx-check-double text-success'></i>
                    <small>10:00 AM</small>
                  </div>
                </div>
                <div class="user-avatar flex-shrink-0 ms-3">
                  <div class="avatar avatar-sm">
                    <img src="https://ui-avatars.com/api/?background=random&name=Â¿?" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <!-- Chat message form -->
        <div class="chat-history-footer" id="message-form-container" style="display: none; margin: 0px;">
          <div class="chat-history-footer">
            <form class="form-send-message d-flex justify-content-between align-items-center" id="send-message-form">
              <input class="form-control message-input border-0 me-3 shadow-none" placeholder="Escriba su mensaje aquí..." id="message-input">
              <div class="message-actions d-flex align-items-center">
                <button class="btn btn-primary d-flex send-msg-btn" type="submit">
                  <i class="bx bx-paper-plane me-md-1 me-0"></i>
                  <span class="align-middle d-md-inline-block d-none">Enviar</span>
                </button>
              </div>
            </form>
          </div>
        </div>
        <!-- Chat message form -->
      </div>
    </div>
    <!-- /Chat History -->
    <div class="app-overlay"></div>
  </div>
</div>

<script>
  function scrollToBottom() {
    const chatHistory = $('.chat-history-body');
    setTimeout(() => {
      chatHistory.scrollTop(chatHistory.prop("scrollHeight"));
    }, 100);
  }

  function resetScroll() {
    $('.chat-history-body').scrollTop(0);
  }

  let isMuted = false;

  $('#mute-button').click(function() {
    $(this).toggleClass('btn-secondary');
    isMuted = !isMuted;
    $(this).find('i').toggleClass('bx-volume-full bx-volume-mute');
    $(this).blur();
    $(this).find('span').text(isMuted ? 'Activar' : 'Silenciar');
  });

  function updateMessageFormVisibility() {
    const activeChatId = $('.app-chat-history').attr('data-active-chat');
    if (activeChatId) {
      $('#message-form-container').show();
    } else {
      $('#message-form-container').hide();
    }
  }

  var notificationSound = new Audio('/assets/audio/notification.mp3');

  function playNotificationSound() {
    if (!isMuted) {
      notificationSound.play().catch(error => console.error("Error al reproducir el sonido de notificaciÃ³n:", error));
    }
  }

  function loadChatMessages(phoneNumberId, contactName, contactAvatarUrl, userAvatarUrl) {
      resetScroll();
      $.ajax({
          url: '{{ route('omnichannel.fetch.messages') }}',
          method: 'GET',
          data: { phone_number: phoneNumberId },
          success: function(response) {
              var chatHistoryBody = $('.chat-history-body ul');
              chatHistoryBody.empty();

              response.messages.forEach(function(message) {
                  var isSender = message.from_phone_id === '{{ auth()->user()->store->phoneNumber->phone_id ?? 'user_phone_number' }}'; // Ajusta segÃºn sea necesario
                  var messageClass = isSender ? 'chat-message-right' : '';
                  var messageElement = $(`<li class="chat-message ${messageClass}"></li>`);

                  const avatarUrl = isSender ? userAvatarUrl : contactAvatarUrl;

                  var avatarElement = `<div class="user-avatar flex-shrink-0 ${isSender ? 'ms-3' : 'me-3'}">
                                          <div class="avatar avatar-sm">
                                              <img src="${avatarUrl}" alt="Avatar" class="rounded-circle">
                                          </div>
                                        </div>`;

                  var messageContent = getMessageContent(message);

                  var messageWrapper = `<div class="d-flex overflow-hidden">
                                            ${isSender ? '' : avatarElement}
                                            <div class="chat-message-wrapper flex-grow-1">
                                                <div class="chat-message-text">
                                                    ${messageContent}
                                                </div>
                                                <div class="text-${isSender ? 'end' : ''} text-muted mt-1">
                                                    <small>${new Date(message.message_created).toLocaleTimeString()}</small>
                                                </div>
                                            </div>
                                            ${isSender ? avatarElement : ''}
                                        </div>`;

                  messageElement.append(messageWrapper);
                  chatHistoryBody.append(messageElement);
                  scrollToBottom();
              });

              $('.chat-history-body').scrollTop($('.chat-history-body')[0].scrollHeight);
          },
          error: function(error) {
              console.error("Error cargando los mensajes: ", error);
          }
      });
  }

  function getMessageContent(message) {
      switch (message.message_type) {
          case 'text':
              return `<p>${message.message_text}</p>`;
          case 'image':
              return `<img src="${message.image_url}" alt="Imagen" style="max-width: 100%; height: auto;">`;
          case 'audio':
              return `<audio controls src="${message.audio_url}"></audio>`;
          case 'document':
              return `<a href="${message.document_url}" target="_blank">Documento</a>`;
          case 'video':
              return `<video controls src="${message.video_url}" style="max-width: 100%;"></video>`;
          case 'sticker':
              return `<img src="${message.sticker_url}" alt="Sticker" style="max-width: 100%; height: auto;">`;
          default:
              return `<p>${message.message_text}</p>`;
      }
  }

  $('#chat-list').on('click', '.chat-contact-list-item:not(.chat-title)', function() {
    $('#chat-list .chat-contact-list-item').removeClass('active');
    $(this).addClass('active');
    const phoneNumberId = $(this).attr('data-phone-number-id');
    const contactName = $(this).attr('data-contact-name');
    const contactAvatarUrl = `https://ui-avatars.com/api/?background=random&name=${encodeURIComponent(contactName)}`;
    const userAvatarUrl = `https://ui-avatars.com/api/?background=random&name={{ urlencode(auth()->user()->name ?? 'NA') }}`;

    const avatarElement = document.querySelector('.avatar-header img');
    avatarElement.src = contactAvatarUrl;

    const chatHeaderInfo = document.querySelector('.chat-header-info h6');
    chatHeaderInfo.textContent = contactName;

    $('.app-chat-history').attr('data-active-chat', phoneNumberId);
    $('.app-chat-history').attr('data-contact-name', contactName);

    updateMessageFormVisibility();
    loadChatMessages(phoneNumberId, contactName, contactAvatarUrl, userAvatarUrl);
  });

  const phoneId = "{{ auth()->user()->store->phoneNumber->phone_id }}";

  function handleNewMessage(message) {
    const currentChatId = $('.app-chat-history').attr('data-active-chat');
    console.log(message, currentChatId)
    if (currentChatId === message.from_phone_id || currentChatId === message.to_phone_id) {
      displayMessage(message, message.from_phone_id === '{{ auth()->user()->store->phoneNumber->phone_id  }}');
    } else if (message.from_phone_id === phoneId) {
        if (currentChatId === message.to_phone_id) {
          displayMessage(message, message.from_phone_id === '{{ auth()->user()->store->phoneNumber->phone_id  }}');
        }
    }
  }

  function displayMessage(message, isSender) {
    var chatHistoryBody = $('.chat-history-body ul');
    var messageClass = isSender ? 'chat-message-right' : '';
    var messageElement = $(`<li class="chat-message ${messageClass}"></li>`);

    const contactName = $('.app-chat-history').attr('data-contact-name');

    const contactAvatarUrl = `https://ui-avatars.com/api/?background=random&name=${encodeURIComponent(contactName)}`;
    const userAvatarUrl = `https://ui-avatars.com/api/?background=random&name={{ urlencode(auth()->user()->name ?? 'NA') }}`;

    const avatarUrl = isSender ? userAvatarUrl : contactAvatarUrl;

    var avatarElement = `<div class="user-avatar flex-shrink-0 ${isSender ? 'ms-3' : 'me-3'}">
                            <div class="avatar avatar-sm">
                                <img src="${avatarUrl}" alt="Avatar" class="rounded-circle">
                            </div>
                          </div>`;

    var messageContent = getMessageContent(message);

    var messageWrapper = `<div class="d-flex overflow-hidden">
                              ${isSender ? '' : avatarElement}
                              <div class="chat-message-wrapper flex-grow-1">
                                  <div class="chat-message-text">
                                      ${messageContent}
                                  </div>
                                  <div class="text-${isSender ? 'end' : ''} text-muted mt-1">
                                      <small>${new Date(message.message_created).toLocaleTimeString()}</small>
                                  </div>
                              </div>
                              ${isSender ? avatarElement : ''}
                          </div>`;

    messageElement.append(messageWrapper);
    chatHistoryBody.append(messageElement);
    scrollToBottom();
  }

  function getMessagePreview(message) {
    switch (message.message_type) {
      case 'image':
        return 'ðŸ“· Imagen';
      case 'audio':
        return 'ðŸ”Š Audio';
      case 'document':
        return 'ðŸ“„ Documento';
      case 'video':
        return 'ðŸŽ¥ Video';
      case 'sticker':
        return 'ðŸŒŸ Sticker';
      default:
        return message.message_text;
    }
  }

  function parseDateString(dateString) {
      if (dateString.includes('T') && dateString.endsWith('Z')) {
          return new Date(dateString);
      } else if (dateString.includes(' ')) {
          const [date, time] = dateString.split(' ');
          return new Date(`${date}T${time}`);
      } else {
          return new Date(dateString);
      }
  }

  function formatDateOrTime(dateString) {
    const messageDate = parseDateString(dateString);
    const now = new Date();
    let diffInSeconds = Math.floor((now - messageDate) / 1000);
    let diffInMinutes = Math.floor((now - messageDate) / 60000);
    let diffInHours = Math.floor((now - messageDate) / 3600000);
    let diffInDays = Math.floor(diffInHours / 24);
    let diffInMonths = Math.floor(diffInDays / 30);
    let diffInYears = Math.floor(diffInDays / 365);

    if (diffInSeconds < 60) {
        return 'hace unos segundos';
    } else if (diffInMinutes < 60) {
        return `hace ${diffInMinutes} minuto${diffInMinutes > 1 ? 's' : ''}`;
    } else if (diffInHours < 24) {
        return `hace ${diffInHours} hora${diffInHours > 1 ? 's' : ''}`;
    } else if (diffInDays < 30) {
        return `hace ${diffInDays} dÃ­a${diffInDays > 1 ? 's' : ''}`;
    } else if (diffInMonths < 12) {
        return `hace ${diffInMonths} mes${diffInMonths > 1 ? 'es' : ''}`;
    } else {
        return `hace ${diffInYears} aÃ±o${diffInYears > 1 ? 's' : ''}`;
    }
  }


  function formatDateToStandard(dateString) {
      const date = new Date(dateString);
      return date.getFullYear() + '-' +
            ('0' + (date.getMonth()+1)).slice(-2) + '-' +
            ('0' + date.getDate()).slice(-2) + ' ' +
            ('0' + date.getHours()).slice(-2) + ':' +
            ('0' + date.getMinutes()).slice(-2) + ':' +
            ('0' + date.getSeconds()).slice(-2);
  }


  function updateChatListOnNewMessage(message, fromPhoneNumberOwner) {
    const chatList = $('#chat-list');
    const existingChat = chatList.find(`[data-phone-number-id="${message.from_phone_id === phoneId ? message.to_phone_id : message.from_phone_id}"]`);
    const firstChatAfterTitle = $('#chat-list .chat-contact-list-item:not(.chat-contact-list-item-title)').first();

    let messagePreview = getMessagePreview(message);
    const formattedDate = formatDateOrTime(message.message_created);
    const formattedMessageCreated = formatDateToStandard(message.message_created);


    if (existingChat.length) {
      existingChat.find('.chat-contact-status').text(messagePreview);
      existingChat.find('small.text-muted').text(formattedDate);
      existingChat.attr('data-message-created', formattedMessageCreated);
      updateChatTimestamps();
      firstChatAfterTitle.before(existingChat);
    } else {
      const chatHtml = `
        <li class="chat-contact-list-item" data-phone-number-id="${message.from_phone_id}" data-contact-name="${fromPhoneNumberOwner}" data-message-created="${message.message_created}">
          <a class="d-flex align-items-center">
            <div class="flex-shrink-0 avatar avatar-online">
              <img src="https://ui-avatars.com/api/?background=random&name=${encodeURIComponent(fromPhoneNumberOwner)}" alt="Avatar" class="rounded-circle">
            </div>
            <div class="chat-contact-info flex-grow-1 ms-3">
              <h6 class="chat-contact-name text-truncate m-0">${fromPhoneNumberOwner}</h6>
              <p class="chat-contact-status text-truncate mb-0 text-muted">${messagePreview}</p>
            </div>
            <small class="text-muted mb-auto">${formattedDate}</small>
          </a>
        </li>`;

      firstChatAfterTitle.before($(chatHtml));
    }


    if ($('#chat-list .chat-title-empty').length) {
      $('#chat-list .chat-title-empty').remove();
    }
  }

  function updateChatTimestamps() {
    const chats = document.querySelectorAll('.chat-contact-list-item');
    chats.forEach(chat => {
      const messageDate = chat.getAttribute('data-message-created');
      if (messageDate) {
        const newTimeAgo = formatDateOrTime(messageDate);
        const timeElement = chat.querySelector('small.text-muted');
        if (timeElement) {
          timeElement.textContent = newTimeAgo;
        }
      }
    });
  }

  function formatDateToSQL(date) {
    function pad(number) {
        return (number < 10 ? '0' : '') + number;
      }

      return date.getFullYear() +
        '-' + pad(date.getMonth() + 1) +
        '-' + pad(date.getDate()) +
        ' ' + pad(date.getHours()) +
        ':' + pad(date.getMinutes()) +
        ':' + pad(date.getSeconds());
  }

  $('#send-message-form').on('submit', function(e) {
    e.preventDefault();
    const messageInput = $('#message-input');
    const messageText = messageInput.val().trim();
    if (!messageText) return;

    const activeChatId = $('.app-chat-history').attr('data-active-chat');
    const contactName = $('.app-chat-history').attr('data-contact-name');

    messageInput.val('');

    $.ajax({
      url: '{{ route('api.send.messages') }}',
      method: 'POST',
      data: {
        phone_number: activeChatId,
        message: messageText,
        from_phone_number_id: '{{ auth()->user()->store->phoneNumber->phone_id }}'
      },
      success: function(response) {
        if (response.status === 'success') {
          const message = {
            message_text: messageText,
            message_type: 'text',
            message_created: formatDateToSQL(new Date()),
            from_phone_id: activeChatId
          };
        } else {
          alert('Error al enviar el mensaje: ' + response.error);
        }
      },
      error: function() {
        alert('Error al enviar el mensaje.');
      }
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    window.Echo.private(`messages.${phoneId}`).listen('.message.received', (e) => {
      if (e.message) {
        handleNewMessage(e.message);
        updateChatListOnNewMessage(e.message, e.fromPhoneNumberOwner);
        playNotificationSound();
      }
    });

    setInterval(updateChatTimestamps, 5000);
  });

</script>
@endsection
