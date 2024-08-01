<?php

namespace App\Http\Controllers;

use App\Services\QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Validation\ValidationException;


class QuotationController extends Controller
{
    private $quotationService;

    public function __construct(QuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    public function getQuotation(Request $request): JsonResponse  //seperate ages, validate, calculate quote w/ service
    {
        try {
            $validated = $request->validate([
                'age' => 'required|string',
                'currency_id' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            $ages = explode(',', $validated['age']);  //string to array

            $quotation = $this->quotationService->calculateQuotation(
                $ages,
                $validated['currency_id'],
                $validated['start_date'],
                $validated['end_date']
            );

            return response()->json($quotation, 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
