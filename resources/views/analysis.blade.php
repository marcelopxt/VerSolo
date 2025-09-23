@extends('layouts.app')

@section('title', 'VerSolo | Início')

@section('content')
  <form action="/report" method="post">
    @csrf
    <label>Selecione arquivo para análise:</label>
    <input type="file" name="files" id="file-input" required>
    <button type="submit">Analisar</button>
  </form>
@endsection