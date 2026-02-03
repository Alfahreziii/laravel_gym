<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td>
                                    <a href="{{ $url }}" class="button button-{{ $color ?? 'primary' }}"
                                        target="_blank" rel="noopener"
                                        style="
    display: inline-block;
    padding: 14px 32px;
    font-size: 16px;
    font-weight: bold;
    color: #ffffff;
    text-decoration: none;
    border-radius: 6px;
    background: linear-gradient(135deg, #ff6b00 0%, #ff8c00 100%);
    box-shadow: 0 4px 12px rgba(255, 107, 0, 0.3);
    font-family: Arial, sans-serif;
">
                                        {{ $slot }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
