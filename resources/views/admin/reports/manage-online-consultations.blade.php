@extends('layouts.admin')

@section('title', 'Manage Online Consultation')

@section('content')
@include('admin.reports.manage-clinic-service-options', [
    'optionGroup' => \App\Models\ClinicServiceOption::GROUP_ONLINE_CONSULTATION,
    'pageTitle' => 'Manage Online Consultation',
    'pageDescription' => 'Manage the doctors or providers shown for online consultation appointments.',
    'addLabel' => 'Add Provider',
    'pageIcon' => 'globe-alt',
    'groupLabel' => 'Online Consultation',
])
@endsection
