@if(session()->has('success') || session()->has('error'))
    <div
        hidden
        aria-hidden="true"
        data-flash-feedback
        @if(session()->has('success')) data-flash-success="{{ session('success') }}" @endif
        @if(session()->has('error')) data-flash-error="{{ session('error') }}" @endif
    ></div>
@endif
