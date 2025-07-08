@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $date }} 기준 유저 등급 분포</h2>

    <table class="table">
        <thead>
            <tr>
                <th>등급</th>
                <th>유저 수</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($result as $segment => $count)
                <tr>
                    <td>{{ $segment }}</td>
                    <td>{{ number_format($count) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection