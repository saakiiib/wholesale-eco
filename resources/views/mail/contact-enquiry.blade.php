Contact form submission
Name: {{ $data['name'] }}
Email: {{ $data['email'] }}
Business: {{ $data['business'] ?? '' }}
Subject: {{ $data['subject'] }}

{{ $data['message'] }}
