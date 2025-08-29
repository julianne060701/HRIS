<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Loan;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    /**
     * Define the loan types based on your database enum.
     */
    protected $loanTypes = [
        'SSS Salary Loan',
        'PAG-IBIG Salary Loan', 
        'SSS Calamity Loan',
        'Other'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loans = Loan::with('employee')->orderBy('created_at', 'desc')->get();
        $employees = Employee::all();
        $loanTypes = $this->loanTypes;

        return view('hr.loan.addloan', compact('loans', 'employees', 'loanTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all();
        $loanTypes = $this->loanTypes;

        return view('hr.loan.create', compact('employees', 'loanTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the inputs
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'loan_type' => ['required', Rule::in($this->loanTypes)],
            'original_amount' => 'required|numeric|min:1',
            'amortization_amount' => 'required|numeric|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'numer_terms' => 'required|integer|min:1',
            'balance' => 'required|numeric|min:0',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Create the loan record
        $loan = Loan::create([
            'employee_id' => $request->input('employee_id'),
            'loan_type' => $request->input('loan_type'),
            'original_amount' => $request->input('original_amount'),
            'balance' => $request->input('balance'),
            'amortization_amount' => $request->input('amortization_amount'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'numer_terms' => $request->input('numer_terms'),
            'status' => 'active',
        ]);

        return redirect()->route('HR.loan.addloan')->with('success', 
            'Loan created successfully! Number of payments: ' . $loan->numer_terms);
    }

    /**
     * Calculate simple loan terms (AJAX endpoint if needed)
     */
    public function calculateLoanTerms(Request $request)
    {
        $request->validate([
            'loan_amount' => 'required|numeric|min:1',
            'monthly_amortization' => 'required|numeric|min:1',
        ]);

        $loanAmount = $request->input('loan_amount');
        $monthlyAmortization = $request->input('monthly_amortization');

        // Simple calculation: loan amount ÷ monthly payment = number of terms
        $numberOfTerms = ceil($loanAmount / $monthlyAmortization);

        return response()->json([
            'number_of_terms' => $numberOfTerms,
            'total_amount' => number_format($loanAmount, 2),
            'monthly_payment' => number_format($monthlyAmortization, 2)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $loan = Loan::with('employee')->findOrFail($id);
        return view('hr.loan.show', compact('loan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $loan = Loan::findOrFail($id);
        $employees = Employee::all();
        $loanTypes = $this->loanTypes;

        return view('hr.loan.edit', compact('loan', 'employees', 'loanTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $loan = Loan::findOrFail($id);

        $request->validate([
            'loan_type' => ['required', Rule::in($this->loanTypes)],
            'original_amount' => 'required|numeric|min:1',
            'balance' => 'required|numeric|min:0',
            'amortization_amount' => 'required|numeric|min:1',
            'numer_terms' => 'required|integer|min:1',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $loan->update([
            'loan_type' => $request->input('loan_type'),
            'original_amount' => $request->input('original_amount'),
            'balance' => $request->input('balance'),
            'amortization_amount' => $request->input('amortization_amount'),
            'numer_terms' => $request->input('numer_terms'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('HR.loan.addloan')->with('success', 'Loan updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $loan = Loan::findOrFail($id);
        
        // Additional check: only allow deletion of active loans with full balance
        if ($loan->balance < $loan->original_amount) {
            return redirect()->back()->withErrors('Cannot delete loan with partial payments made.');
        }

        $loan->delete();
        return redirect()->route('HR.loan.addloan')->with('success', 'Loan deleted successfully!');
    }

    /**
     * Generate simple payment schedule
     */
    public function generatePaymentSchedule(string $id)
    {
        $loan = Loan::with('employee')->findOrFail($id);
        
        $schedule = [];
        $remainingBalance = $loan->original_amount;
        $monthlyPayment = $loan->amortization_amount;
        $currentDate = new \DateTime($loan->start_date);
        
        for ($payment = 1; $payment <= $loan->numer_terms; $payment++) {
            $paymentAmount = min($monthlyPayment, $remainingBalance);
            $remainingBalance -= $paymentAmount;
            
            $schedule[] = [
                'payment_number' => $payment,
                'due_date' => $currentDate->format('Y-m-d'),
                'payment_amount' => $paymentAmount,
                'remaining_balance' => max(0, $remainingBalance),
                'status' => 'pending'
            ];
            
            // Move to next month
            $currentDate->modify('+1 month');
            
            if ($remainingBalance <= 0) break;
        }
        
        return view('hr.loan.payment_schedule', compact('loan', 'schedule'));
    }

    /**
     * Record a loan payment
     */
    public function recordPayment(Request $request, string $id)
    {
        $loan = Loan::findOrFail($id);
        
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $loan->balance,
            'payment_date' => 'required|date',
        ]);
        
        $paymentAmount = $request->input('payment_amount');
        
        // Update loan balance
        $loan->balance = max(0, $loan->balance - $paymentAmount);
        
        // Update status if loan is fully paid
        if ($loan->balance <= 0) {
            $loan->status = 'completed';
        }
        
        $loan->save();
        
        return redirect()->route('loan.show', $id)->with('success', 
            'Payment recorded successfully! Remaining balance: ₱' . number_format($loan->balance, 2));
    }
}