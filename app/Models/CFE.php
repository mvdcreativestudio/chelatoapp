<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CFE extends Model
{
    use HasFactory;

    protected $table = 'cfes';

    protected $fillable = [
      'order_id', 'store_id', 'type', 'serie', 'nro',
      'caeNumber', 'caeRange', 'caeExpirationDate', 'total', 'currency', 'status',
      'emitionDate', 'sentXmlHash', 'securityCode', 'qrUrl', 'cfeId', 'reason', 'balance', 'main_cfe_id', 'received', 'is_receipt', 'issuer_name'
    ];

    protected $casts = [
      'caeRange' => 'array',
      'caeExpirationDate' => 'date',
      'emitionDate' => 'datetime',
    ];

    /**
     * Mapea estados de CFE con sus traducciones.
     */
    private static array $statusMap = [
        'CFE_UNKNOWN_ERROR' => 'Error desconocido',
        'CREATED' => 'Creado',
        'PROCESSED_ACCEPTED' => 'Procesado y Aceptado',
        'PROCESSED_REJECTED' => 'Procesado y Rechazado',
        'FORMAT_REJECTED' => 'Rechazado por Formato',
        'SCHEDULED' => 'Programado',
    ];

    /**
     * Mapea los tipos de CFE con sus traducciones.
     */
    private static array $typeMap = [
        101 => 'ET', // E-Ticket
        102 => 'ET-NC', // E-Ticket Nota de Crédito
        103 => 'ET-ND', // E-Ticket Nota de Débito
        111 => 'EF', // E-Factura
        112 => 'EF-NC', // E-Factura Nota de Crédito
        113 => 'EF-ND', // E-Factura Nota de Débito
    ];

    /**
     * Mapa de tipos de CFE si esta como is_receipt
     */

    private static array $typeMapReceipt = [
        101 => 'ET-R', // E-Ticket Recibo
        111 => 'EF-R', // E-Factura Recibo
    ];
    /**
     * Obtiene el estado en español desde su clave en inglés.
     *
     * @param string|null $status
     * @return string
     */
    public static function getTranslatedStatus(?string $status): string
    {
        return self::$statusMap[$status] ?? 'Estado Desconocido';
    }

    /**
     * Obtiene la clave en inglés desde su traducción en español.
     *
     * @param string|null $translatedStatus
     * @return string|null
     */
    public static function getStatusFromTranslation(?string $translatedStatus): ?string
    {
        $flippedMap = array_flip(self::$statusMap);
        return $flippedMap[$translatedStatus] ?? null;
    }

    /**
     * Obtiene la clave del tipo de CFE a partir de su traducción.
     */
    public static function getTypeFromTranslation(string $translatedType): ?int
    {
        $typeKey = array_search($translatedType, self::$typeMap, true);

        if ($typeKey === false) {
            $typeKey = self::getTranslatedTypeReceipt($translatedType);
        }
        return $typeKey !== false ? $typeKey : null;
    }

    /**
     * Obtiene la traducción del tipo de CFE si is_receipt es true
     */
    public static function getTranslatedTypeReceipt(string $type): string
    {
        $typeKeyIsReceipt = array_search($type, self::$typeMapReceipt, true);
        return $typeKeyIsReceipt !== false ? $typeKeyIsReceipt : 'Tipo Desconocido';
    }
    /**
     * Obtiene la orden asociada al recibo.
     *
     * @return BelongsTo
    */
    public function order(): BelongsTo
    {
      return $this->belongsTo(Order::class);
    }

    /**
     * Obtiene la tienda asociada al recibo.
     *
     * @return BelongsTo
    */
    public function store(): BelongsTo
    {
      return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene CFE principal
     *
     * @return BelongsTo
    */
    public function mainCfe(): BelongsTo
    {
      return $this->belongsTo(CFE::class, 'main_cfe_id');
    }

    /**
     * Obtiene los CFEs asociados al CFE principal
     *
     * @return HasMany
    */
    public function relatedCfes(): HasMany
    {
      return $this->hasMany(CFE::class, 'main_cfe_id');
    }
}
