<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationFormController extends Controller
{
    public function showForm()
    {

        return view('quotation-form');    }
}
