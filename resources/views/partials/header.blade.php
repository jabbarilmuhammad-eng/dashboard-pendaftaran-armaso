@if($settingweb && $settingweb->logo_website)
    <link rel="icon"
        type="image/x-icon"
        href="{{ asset('storage/' . $settingweb->logo_website) }}">
@endif

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta name="author" content="difarft.com">

<meta name="description"
    content="{{ $settingweb?->deskripsi_website ?? 'Website ARMASO' }}">