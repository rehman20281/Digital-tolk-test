<?php
/**
 * Created by PhpStorm.
 * User: Abdul Rehman
 * Date: 12/05/2023
 * Time: 3:15 PM
 */

namespace DTApi\Services;

use DTApi\Repository\BookingRepository;
use http\Client\Response;

class BookingService
{
    public function __construct(protected BookingRepository $repository)
    {
    }

    public function getBookingRepository(): BookingRepository
    {
        return $this->repository;
    }

    public function update(int $id, Request $request): Response
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
    }

    public function jobEmail(Request $request): ?Response
    {
        $data = $request->all();

        if ($response = $this->repository->storeJobEmail($data)) {
            return response($response);
        }

        return null;
    }

    public function getTranslatorJobUser(int $id): ?Response
    {
        if ($job = $this->repository->with('translatorJobRel.user')->find($id)) {
            return response($job);
        }

        return null;
    }

    public function getDistanceFeed(Request $request)
    {
        $data = $request->all();

        if (isset($data['distance']) && $data['distance'] != '') {
            $distance = $data['distance'];
        } else {
            $distance = '';
        }
        if (isset($data['time']) && $data['time'] != '') {
            $time = $data['time'];
        } else {
            $time = '';
        }
        if (isset($data['jobid']) && $data['jobid'] != '') {
            $jobid = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != '') {
            $session = $data['session_time'];
        } else {
            $session = '';
        }

        if ($data['flagged'] == 'true') {
            if ($data['admincomment'] == '') {
                return 'Please, add comment';
            }
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }

        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        } else {
            $manually_handled = 'no';
        }

        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        } else {
            $by_admin = 'no';
        }

        if (isset($data['admincomment']) && $data['admincomment'] != '') {
            $admincomment = $data['admincomment'];
        } else {
            $admincomment = '';
        }
        if ($time || $distance) {
            Distance::where('job_id', '=', $jobid)->update(['distance' => $distance, 'time' => $time]);
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobid)->update(['admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin]);
        }

        return response('Record updated!');
    }

    public function resendNotification(Request $request)
    {
        $data = $request->all();
        if ($job = $this->bookingService->getBookingRepository()->find($data['jobid'])) {
            $job_data = $this->bookingService->getBookingRepository()->jobToData($job);
            $this->bookingService->getBookingRepository()->sendNotificationTranslator($job, $job_data, '*');

            return response(['success' => 'Push sent']);
        }

        return response(['success' => 'Not sent']);
    }

    public function resendSMSNotification(Request $request)
    {
        $data = $request->all();

        try {
            $job = $this->bookingService->getBookingRepository()->find($data['jobid']);
            $this->bookingService->getBookingRepository()->jobToData($job);
            $this->bookingService->getBookingRepository()->sendSMSNotificationToTranslator($job);

            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }
}
