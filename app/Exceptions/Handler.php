<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];
    protected $dontReport = [];
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'image_data',
    ];

    protected function invalid($request, ValidationException $exception)
    {
        if (!$request->routeIs(
            'store.health.form*',
            'admin.settings.health-profile.form.store',
            'dependent.profile.store'
        )) {
            return parent::invalid($request, $exception);
        }

        if ($this->shouldReturnJson($request, $exception)) {
            return $this->invalidJson($request, $exception);
        }

        return redirect($exception->redirectTo ?? url()->previous())
            ->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            if (!$e instanceof \PDOException || !$request->is('student/booking', 'student/appointments/store')) {
                return null;
            }

            $bookingFields = ['date', 'time', 'service', 'remarks'];
            $input = $request->isMethod('POST')
                ? $request->only($bookingFields)
                : $request->session()->getOldInput();
            $bookingInput = array_intersect_key($input, array_flip($bookingFields));
            $bookingInput = array_filter($bookingInput, fn ($value) => is_scalar($value));

            return response()->view('errors.database-unavailable', [
                'bookingInput' => $bookingInput,
            ], 503);
        });
    }
}
