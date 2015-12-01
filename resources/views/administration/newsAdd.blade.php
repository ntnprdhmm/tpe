@extends('administration.skeleton')

@section('wrapper')
    @parent
    @section('pageTitle')
        <h1>News <small>write a news</small></h1>
    @endsection
    @section('content')
        <form method="post" action="{{ url('/administration/news/add') }}">
            {!! csrf_field() !!}
            <div class="form-group">
                <label for="title">Title <span id="result" class="result"></span></label>
                <input type="text" class="form-control" id="title" name="title" maxlength="200" placeholder="Title of the news" value="{{ old('title') }}" required>
            </div>
            <div class="form-group">
                <label for="centent">Content</label>
                <textarea name="content" id="content" value="{{ old('content') }}"></textarea>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success">Validate</button>
                <a href="" class="btn btn-warning" role="button">Cancel</a>
            </div>
        </form>
    @endsection
@endsection
@section('scripts')
    {!! HTML::script('js/tinymce/tinymce.min.js') !!}
    {!! HTML::script('js/tinymceConf.js') !!}
@endsection