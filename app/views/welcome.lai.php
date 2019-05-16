@extends(layout)

@section(title, Welcome)

@section(main){
    <div>Welcome {{ $id ?? null }}</div>
}