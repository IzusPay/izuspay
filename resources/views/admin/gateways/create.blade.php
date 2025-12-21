@extends('layouts.app')
@section('content')
<div class="p-6">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-white/5 p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Nova Adquirente</h1>
        </div>
        <form action="{{ route('admin.gateways.store') }}" method="POST">
            @include('admin.gateways._form')
        </form>
    </div>
</div>
@endsection
