@extends('layouts.admin')

@section('title', 'Manage Other Services')

@section('content')
@include('admin.reports.manage-clinic-service-options', [
    'optionGroup' => \App\Models\ClinicServiceOption::GROUP_OTHER_SERVICE,
    'pageTitle' => 'Manage Other Services',
    'pageDescription' => 'Manage additional clinic services that can be selected for appointments and consultations.',
    'addLabel' => 'Add Service',
    'pageIcon' => 'plus-circle',
    'groupLabel' => 'Other Services',
])
@endsection
