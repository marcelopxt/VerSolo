@extends('layouts.app')

@section('title', 'VerSolo | Início')

@section('content')
  @if (!empty($filename))
    <h1>{{$filename}}</h1>
  @endif
  <form action="/report" method="post" enctype="multipart/form-data">
    @csrf
    <label>Selecione arquivo para análise:</label>
    <input type="file" name="file" id="file-input" required>
    <button type="submit">Analisar</button>
  </form>
@endsection