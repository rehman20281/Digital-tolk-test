<?php

namespace DTApi\Http\Controllers;

use DTApi\Services\BookingService;
use Illuminate\Http\Request;

/**
 * Class BookingController
 */
class BookingController extends Controller
{
    /**
     * BookingController constructor.
     */
    public function __construct(protected BookingService $bookingService)
    {
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->bookingService->getBookingRepository()->getUsersJobs($user_id);
        } elseif ($request->__authenticatedUser->user_type == config('admin.roleid') || $request->__authenticatedUser->user_type == config('superadmin.roleid')) {
            $response = $this->bookingService->getBookingRepository()->getAll($request);
        }

        return response($response);
    }

    /**
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->bookingService->getBookingRepository()->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingService->getBookingRepository()->store($request->__authenticatedUser, $data);

        return response($response);
    }

    /**
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->bookingService->getBookingRepository()->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
    }

    /**
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingService->getBookingRepository()->storeJobEmail($data);

        return response($response);
    }

    /**
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->bookingService->getBookingRepository()->getUsersJobsHistory($user_id, $request);

            return response($response);
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingService->getBookingRepository()->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->bookingService->getBookingRepository()->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingService->getBookingRepository()->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingService->getBookingRepository()->endJob($data);

        return response($response);
    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingService->getBookingRepository()->customerNotCall($data);

        return response($response);
    }

    /**
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingService->getBookingRepository()->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
    }

    public function reOpen(Request $request)
    {
        $data = $request->all();
        $response = $this->bookingService->getBookingRepository()->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        return $this->bookingService->resendNotification($request);
    }

    /**
     * Sends SMS to Translator
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        return $this->bookingService->resendSMSNotification($request);
    }
}
