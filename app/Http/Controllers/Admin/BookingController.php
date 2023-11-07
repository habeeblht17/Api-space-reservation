<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Space;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Admin\BookingResource;
use App\Http\Requests\Admin\Booking\StoreRequest;
use App\Models\Plan;

class BookingController extends Controller
{
    /**
     * List all Booking
     */
    public function index()
    {
        $booking = Booking::with(['users', 'spaces'])->latest()->paginate();
        return BookingResource::collection($booking);
    }



    /**
     * Store Booking logic
     */
    public function store(StoreRequest $request)
    {
        // $spaceId = $request->space_id;
        // $startTime = Carbon::parse($request->start_time);
        // $endTime = Carbon::parse($request->end_time);

        // //check for availability of space
        // if(!$this->isSpaceAvailable($spaceId, $startTime, $endTime))
        // {
        //     return response()->json([
        //         'message' => 'The space is not available during the selected time range.'
        //     ], 422);
        // }

        //Save booking to database
        $booking = Booking::Create([
            'space_id' => $request->space_id,
            'user_id' => auth()->user()->id,
            'email' => auth()->user()->email,
            'plan_id' => $request->plan_id,
            'interval_count' => $request->interval_count,
        ]);

        $bookingId = $booking->id;

        return redirect()->route('payment', $bookingId);

        // return (new BookingResource($booking))->additional([
        //     'message' => "Booking Successfully",
        // ]);
    }



    /**
     * Payment logic
     */
    public function makePayment(Request $request, $bookingId)
    {
        $bookingIdInfo = Booking::findOrFail($bookingId);

        $planId = $bookingIdInfo->plan_id;
        $spaceId = $bookingIdInfo->space_id;
        $userId = $bookingIdInfo->user_id;
        $userEmail = $bookingIdInfo->email;
        $intervalCount = $bookingIdInfo->interval_count;

        //Calculation for Booking amount based on subscription type
        $amount = $this->calculateSubscriptionAmount($planId, $spaceId, $intervalCount);

        try {

            //implement transaction on paystack
            $fields = [
                'email' => $userEmail,
                'amount' => $amount * 100,
                'currency' => 'NG',
                'callback_url' => route('payment.callback'),
                'channels' => 'card',
                'metadata' => [
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                ]
            ];

            $payment = json_decode($this->payment_initiate($fields));

            // Check for successful connection with paystack.
            if($payment) {

                //check for payment status
                if ($payment->status === true) {

                    //Match the booking id from paystack with the one stored in the database
                    $booking = Booking::findOrFail($payment->data->metadata->booking_id);

                    if(!$booking) {
                        return response()->json([
                            'message' => 'Unable to find booking.'
                        ], 403);
                    }

                    // Store the payment reference in the bookings table
                    $booking->Payment_reference = $payment->data->reference;
                    $booking->save();

                    //redirect the user to paystack payment page
                    return redirect($payment->data->authorization_url);

                } else {
                    return response()->json($payment->message);
                }

            } else {
                return response()->json([
                    'message' => 'Something is wrong, kindly check your connection and try again again.',
                ]);
            }

        } catch (\Exception $e) {
            //Handle Outer unexpected errors
            return response()->json([
                'message' => 'An unexpected error occurred. Please try again later.',
            ]);
        }

    }




    /**
     *
     * Handle Paystack callback url
     *
     */
    public function paymentCallback(Request $request)
    {
        $paymentDetails = json_decode($this->verify_payment($request->reference));

        if($paymentDetails->status === true) {

            // Check if the payment was successful
            if ($paymentDetails->data->status === 'success') {

                // Retrieve the booking based on the reference passed back by Paystack
                $booking = Booking::where('payment_reference', $paymentDetails->data->reference)->first();

                if (!$booking) {
                    // Handle booking not found
                    return response()->json(['message' => 'Booking reference not found'], 404);
                }

                // Update the booking status and save the payment reference
                $booking->payment_status = 'paid';
                $booking->payment_reference = $paymentDetails->data->reference;
                $booking->save();

                //Send email notification to user
                //Mail::to(auth()->user()->email)->send(new BookingConfirmationMail($booking));

                //Send email notification to Space owner
                //Mail::to('owner@example.com')->send(new BookingConfirmationMail($booking));

                return response()->json(['message' => 'Booking successfully paid'], 200);

            } else {
                return response()->json($paymentDetails->message);
            }

        } else {
            return response()->json([
                'message' => 'Something is wrong, kindly check your connection and try again again.',
            ]);
        }
    }



    /**
     *
     * Handle Paystack payment verification
     *
     */
    private function verify_payment($reference)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer SECRET_KEY",
            "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return response()->json($err);
        } else {
            return $response;
        }

    }



    /**
     *
     * Handle Paystack payment
     *
     */
    private function payment_initiate($fields)
    {

        $url = "https://api.paystack.co/transaction/initialize";

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $response = curl_exec($ch);

        return $response;

    }



    /**
     *
     * Handle Booking Amount Calculation
     * Based on Subscription.
     */
    private function calculateSubscriptionAmount($planId, $spaceId, $intervalCount)
    {

        // $start = Carbon::parse($startTime);
        // $end = Carbon::parse($endTime);
        // $days = $end->diffInDays($start);

        $space = Space::findOrFail($spaceId);
        $plan = Plan::findOrFail($planId);
        $interval = $plan->interval;

        switch($interval) {
            case 'day':
                $ratePerUnit = $space->rate_per_unit;
                $totalSpaceMeasurement = $space->measurement;
                return $intervalCount * $ratePerUnit * $totalSpaceMeasurement;
            case 'week':
                $ratePerUnit = $space->rate_per_unit;
                $totalSpaceMeasurement = $space->measurement;
                return $intervalCount * $ratePerUnit * $totalSpaceMeasurement;
            case 'month':
                $ratePerUnit = $space->rate_per_unit;
                $totalSpaceMeasurement = $space->measurement;
                return $intervalCount * $ratePerUnit * $totalSpaceMeasurement;
            case 'year':
                $ratePerUnit = $space->rate_per_unit;
                $totalSpaceMeasurement = $space->measurement;
                return $intervalCount * $ratePerUnit * $totalSpaceMeasurement;
            default:
            return 0;
        }
    }


    /**
     * Handle Subcription cancellation.
     */
    private function cancel($id)
    {
        $booking = Booking::findorfail($id);

        //Check if the user is authourized cancel the subscription
        if(auth()->user()->id !== $booking->user_id)
        {
            return response()->json([
                'message' => 'You are not authorized to cancel this booking.',
            ], 403);
        }

        //Check if the subscription has been canceled before.
        if($booking->cancel_at)
        {
            return response()->json([
                'message' => 'This booking is already canceled',
            ], 422);
        }

        $booking->update([
            'canceled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Booking canceled successfully.',
        ]);
    }

    /**
     * Handle Space Availability.
     */
    private function isSpaceAvailable($spaceId, $startTime, $endTime)
    {
        $existingBookings = Booking::where('space_id', $spaceId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween('end_time', [$startTime, $endTime]);
            })
            ->count();

        return $existingBookings === 0;
    }
}
