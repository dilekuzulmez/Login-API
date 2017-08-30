@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-user-circle" aria-hidden="true"></i>
                    <label class="control-label">{{ $user->name ?: 'Belirtilmedi' }}</label>
                </div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    Giriş yaptınız!
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
