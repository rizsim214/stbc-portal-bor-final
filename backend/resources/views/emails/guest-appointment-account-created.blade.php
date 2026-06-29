<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your STBC appointment account details</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <p>Hello {{ $patientName }},</p>

    <p>Your appointment request has been submitted successfully, and a patient account has been created for you.</p>

    <p>
        Temporary password: <strong>{{ $temporaryPassword }}</strong>
    </p>

    <p>
        Appointment schedule:
        <strong>{{ $appointment->start_time }}</strong> to
        <strong>{{ $appointment->end_time }}</strong>
    </p>

    <p>Please sign in using your email address and this temporary password, then change it from your account page as soon as possible.</p>

    <p>STBC</p>
</body>
</html>
