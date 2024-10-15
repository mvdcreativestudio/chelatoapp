<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Repositories\CashRegisterLogRepository;

class CheckOpenCashRegister
{
    protected $cashRegisterLogRepository;

    public function __construct(CashRegisterLogRepository $cashRegisterLogRepository)
    {
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = auth()->user()->id;
        $openCashRegisterId = $this->cashRegisterLogRepository->hasOpenLogForUser($userId);

        if ($openCashRegisterId) {
            return $next($request);
            // return redirect()->route('pdv.front');
        } else {
            return redirect()->route('points-of-sales.index');
        }
    }
}
