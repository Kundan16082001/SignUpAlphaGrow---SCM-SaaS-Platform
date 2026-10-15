<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, PosTransaction};
use App\Mail\{InvoiceMail, PosReceiptMail};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class EmailController extends Controller
{
    public function sendInvoice(Request $request, Invoice $invoice)
    {
        if ($invoice->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'recipient_email' => 'required|email',
            'subject' => 'nullable|string|max:255',
        ]);

        try {
            $mail = new InvoiceMail($invoice);
            if ($request->filled('subject')) {
                $mail->subject($request->subject);
            }
            
            Mail::to($request->recipient_email)->send($mail);

            return redirect()->back()->with('success', "Invoice sent successfully to {$request->recipient_email}!");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['email' => 'Failed to send: ' . $e->getMessage()]);
        }
    }

    public function sendReceipt(Request $request, PosTransaction $transaction)
    {
        if ($transaction->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate(['recipient_email' => 'required|email']);

        try {
            Mail::to($request->recipient_email)->send(new PosReceiptMail($transaction));
            return response()->json(['success' => true, 'message' => 'Receipt sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Test email route (for debugging SMTP)
    public function testEmail()
    {
        try {
            Mail::raw('This is a test email from SignupAlphaGrow. SMTP is working!', function ($message) {
                $message->to(Auth::user()->email)->subject('✓ SignupAlphaGrow Test Email');
            });
            return back()->with('success', 'Test email sent! Check your inbox.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Test failed: ' . $e->getMessage()]);
        }
    }
}