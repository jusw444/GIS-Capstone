@extends('layouts.error')

@section('title', 'Internal Server Error')
@section('code', 500)
@section('message', 'Oops! Something went wrong on our side.')
@section('image', asset('images/500.jpg'))