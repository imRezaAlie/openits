<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>OpenITS Contact Message</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
    <h2 style="margin: 0 0 1rem;">New contact form submission</h2>

    <p><strong>Name:</strong> {{ $senderName }}</p>
    <p><strong>Email:</strong> {{ $senderEmail }}</p>
    <p><strong>Subject:</strong> {{ $subjectLine }}</p>

    <p><strong>Message:</strong></p>
    <p style="white-space: pre-wrap;">{{ $messageBody }}</p>
</body>
</html>
