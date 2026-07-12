<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

/** One-click, signed unsubscribe from marketing messages (linked in every campaign email). */
class PublicUnsubscribeController extends Controller
{
    public function __invoke(Request $request, int $customer)
    {
        abort_unless($request->hasValidSignature(), 403);

        Customer::withoutGlobalScopes()->whereKey($customer)
            ->update(['marketing_consent' => false]);

        return response('<html><body style="font-family:sans-serif;text-align:center;padding:60px;">'
            .'<h2>You have been unsubscribed.</h2><p>You will no longer receive marketing messages from this store.</p>'
            .'</body></html>', 200)->header('Content-Type', 'text/html');
    }
}
