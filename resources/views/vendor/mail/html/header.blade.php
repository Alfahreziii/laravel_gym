@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <div style="text-align: center;">
                <h1
                    style="color: #ff6b00; font-size: 28px; font-weight: bold; margin: 0; font-family: Arial, sans-serif;">
                    💪 {{ $slot }}
                </h1>
                <p style="color: #666; font-size: 12px; margin: 5px 0 0 0; font-family: Arial, sans-serif;">
                    Your Fitness Journey Starts Here
                </p>
            </div>
        </a>
    </td>
</tr>
