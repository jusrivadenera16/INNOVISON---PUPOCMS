@extends('layouts.admin')

@section('title', 'Manage Referral Services')

@section('content')
@include('admin.reports.manage-clinic-service-options', [
    'optionGroup' => \App\Models\ClinicServiceOption::GROUP_REFERRAL,
    'pageTitle' => 'Manage Referral Services',
    'pageDescription' => 'Manage the referral choices available when recording a clinic consultation.',
    'addLabel' => 'Add Referral',
    'pageIcon' => 'arrow-long-right',
    'groupLabel' => 'Referral Services',
])
@endsection
