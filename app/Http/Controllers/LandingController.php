<?php

namespace App\Http\Controllers;

use App\Repositories\LandingRepository;
use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;


class LandingController extends Controller
{

    /**
     * The Landing repository.
     *
     * @var LandingRepository
     */
    protected $landingRepository;

    /**
     * Injects the repository into the controller.
     *
     * @param LandingRepository $landingRepository
     */
    public function __construct(LandingRepository $landingRepository)
    {
        $this->landingRepository = $landingRepository;
    }

    /**
     * Displays the home page.
     *
     * @return View
     */
    public function index(): View
    {
        return view('content.landing.index');
    }

    /**
     * Displays products with their categories.
     *
     * @return \Illuminate\View\View
     */
    public function products(): View
    {
        $products = $this->landingRepository->getProductsWithCategories();
        $categories = $this->landingRepository->getAllCategories();

        return view('content.landing.products', compact('products', 'categories'));
    }

    /**
     * Displays the individual product view
     *
     * @param int $id
     * @return View
     */
    public function showProduct($id): View
    {
        $product = $this->landingRepository->getProductById($id);
        return view('content.landing.product-detail', compact('product'));
    }

    /**
     * Filters products by category.
     *
     * @param int|null $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterProducts($categoryId = null): \Illuminate\Http\JsonResponse
    {
        $products = $categoryId
            ? Product::whereHas('categories', function ($query) use ($categoryId) {
                $query->where('product_categories.id', $categoryId); // Prefix 'product_categories'
            })->get()
            : Product::all();

        // Renders the partial view with filtered products
        $html = view('content.landing.partials.products', compact('products'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * View of the About Us page
     *
     * @return View
     */
    public function aboutUs(): View
    {
        return view('content.landing.about-us');
    }


    /**
     * View of the contact page
     *
     * @return View
     */
    public function contact(): View
    {
        return view(view: 'content.landing.contact');
    }

    /**
     * Sends the contact form
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendContact(Request $request)
    {
        // Validar datos
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'message' => 'required|string|max:1000',
            'company' => 'nullable|string|max:255',
        ]);

        // Obtener la configuración de la empresa
        $companySettings = app('App\Models\CompanySettings')->first();

        // Enviar correo (opcional, ajusta según tus necesidades)
        Mail::send([], [], function ($message) use ($validatedData, $companySettings) {
            $htmlContent =
                'Nombre: ' . $validatedData['name'] . '<br>' .
                'Correo: ' . $validatedData['email'] . '<br>' .
                'Teléfono: ' . ($validatedData['phone'] ?? 'No proporcionado') . '<br>' .
                'Empresa: ' . ($validatedData['company'] ?? 'No proporcionado') . '<br>' .
                'Mensaje: ' . $validatedData['message'];

            $message->to($companySettings->email ?? 'info@mvdcreativestudio.com')
                    ->from('no-reply@anjos.com.uy', $companySettings->name ?? 'MVD Studio')
                    ->subject('Nuevo mensaje del Sitio Web')
                    ->replyTo($validatedData['email'])
                    ->html($htmlContent);
        });

        // Redirigir con mensaje de éxito
        return redirect()->back()->with('success', '¡Mensaje enviado correctamente!');
    }

}
