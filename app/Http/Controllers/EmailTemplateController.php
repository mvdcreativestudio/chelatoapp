<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Storage;


class EmailTemplateController extends Controller
{
    /**
     * Muestra la página de edición de plantillas de correos
     *
     * @return \Illuminate\View\View
     */
    public function edit($templateId = null)
    {
        $templates = EmailTemplate::all();
        $template = $templateId ? EmailTemplate::findOrFail($templateId) : $templates->first();
        return view('content.e-commerce.backoffice.email-templates', compact('template', 'templates'));
    }

    /**
     * Actualiza la plantilla de correo
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $templateId)
    {
        $request->validate([
            'template_subject' => 'required',
            'template_body' => 'required',
        ]);

        $template = EmailTemplate::findOrFail($templateId);
        $template->update([
            'subject' => $request->template_subject,
            'body' => $request->template_body,
        ]);

        return redirect()->route('email-templates.edit', $template->id)->with('success', 'Plantilla actualizada correctamente.');
    }

    /**
     * Maneja la carga de imágenes para el editor Quill
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        if($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            return response()->json(['url' => Storage::url($path)]);
        }
        return response()->json(['error' => 'No se ha proporcionado ninguna imagen.'], 400);
    }

    public function testEmail()
    {
        $order_id = 1234;
        $order_date = '2024-05-23 23:39:23';
        $order_payment_method = 'efectivo';
        $client_name = 'Martín';
        $client_lastname = 'Santamaría';
        $client_email = 'email@example.com';
        $order_shipping_method = 'pickup';
        $client_address = 'Dirección';
        $client_city = 'Ciudad';
        $client_state = 'Estado';
        $client_phone = '123456789';
        $order_items = '<tr><td style="text-align: left;">Producto x1</td><td style="text-align: right;">$200</td></tr>';
        $order_subtotal = 200;
        $order_shipping = 0;
        $coupon_amount = 0;
        $order_total = 200;
        $store_name = 'Pocitos';
        $ecommerce_name = 'Sumeria';


        return view('emails.ecommerce.customer.new-order-client', compact(
            'order_id',
            'order_date',
            'order_payment_method',
            'client_name',
            'client_lastname',
            'client_email',
            'order_shipping_method',
            'client_address',
            'client_city',
            'client_state',
            'client_phone',
            'order_items',
            'order_subtotal',
            'order_shipping',
            'coupon_amount',
            'order_total',
            'store_name',
            'ecommerce_name'
        ));
    }

}
