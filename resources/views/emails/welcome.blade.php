<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to NAPTIN Cooperative</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%); padding: 30px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700;">NAPTIN Staff Thrift Cooperative</h1>
                            <p style="color: #bfdbfe; margin: 5px 0 0; font-size: 12px; text-transform: uppercase; letter-spacing: 2px;">Membership Welcome</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #1f2937; margin: 0 0 15px; font-size: 20px;">Welcome, {{ $member->first_name }}!</h2>
                            <p style="color: #4b5563; line-height: 1.6; margin: 0 0 20px;">
                                Your membership with the NAPTIN Staff Thrift Cooperative Society has been successfully registered. Below are your account details:
                            </p>

                            {{-- Details Box --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Staff ID</td>
                                                <td style="padding: 6px 0; color: #1f2937; font-size: 13px; font-weight: 600; text-align: right;">{{ $member->staff_id_display }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Full Name</td>
                                                <td style="padding: 6px 0; color: #1f2937; font-size: 13px; font-weight: 600; text-align: right;">{{ $member->first_name }} {{ $member->last_name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Email</td>
                                                <td style="padding: 6px 0; color: #1f2937; font-size: 13px; text-align: right;">{{ $user->email }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Region</td>
                                                <td style="padding: 6px 0; color: #1f2937; font-size: 13px; text-align: right;">{{ $member->region->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Status</td>
                                                <td style="padding: 6px 0; font-size: 13px; text-align: right;">
                                                    <span style="background-color: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 600;">{{ ucfirst($member->status) }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Temporary Password --}}
                            <div style="background-color: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 15px 20px; margin: 20px 0;">
                                <p style="color: #92400e; margin: 0; font-size: 13px; font-weight: 600;">Your Login Credentials</p>
                                <p style="color: #78350f; margin: 8px 0 0; font-size: 13px;">
                                    <strong>Email:</strong> {{ $user->email }}<br>
                                    <strong>Temporary Password:</strong> <code style="background: #fff; padding: 2px 6px; border-radius: 4px; font-size: 14px; font-weight: 700; color: #dc2626;">{{ $temporaryPassword }}</code>
                                </p>
                            </div>

                            <p style="color: #4b5563; line-height: 1.6; font-size: 13px; margin: 20px 0 0;">
                                Please log in using the temporary password above and change it immediately for your security. You can access the member portal to view your savings, shares, loans, and make product orders.
                            </p>

                            {{-- CTA Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0 10px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('login') }}" style="background-color: #1d4ed8; color: #ffffff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-block;">Login to Portal</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 40px; border-top: 1px solid #e5e7eb; text-align: center;">
                            <p style="color: #9ca3af; font-size: 11px; margin: 0;">
                                NAPTIN Staff Thrift Cooperative Society &middot; This is an automated message.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
