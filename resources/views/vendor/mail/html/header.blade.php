@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
    <img src="{{ asset('images/logo.png') }}" class="logo" alt="{{ config('app.name') }}" style="height: 70px; width: auto; display: block; margin: 0 auto;">
</a>
</td>
</tr>
