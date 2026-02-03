<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    <p style="color: #999; font-size: 12px; margin: 0; font-family: Arial, sans-serif;">
                        © {{ date('Y') }} Kenzo Fitness Center. All rights reserved.
                    </p>
                    <p style="color: #999; font-size: 11px; margin: 10px 0 0 0; font-family: Arial, sans-serif;">
                        Build Your Body, Build Your Life
                    </p>
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                </td>
            </tr>
        </table>
    </td>
</tr>
