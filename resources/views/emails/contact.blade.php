<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: -apple-system, Arial, sans-serif; background: #f4f4f7; margin: 0; padding: 24px; }
  .wrap { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid #e4e4e7; }
  .head { background: #0c0b1a; padding: 28px 32px; }
  .head h2 { color: #daa520; margin: 0; font-size: 18px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .head p { color: rgba(255,255,255,.5); margin: 4px 0 0; font-size: 13px; }
  .body { padding: 28px 32px; }
  .row { margin-bottom: 20px; }
  .label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px; }
  .value { font-size: 15px; color: #111; font-weight: 500; }
  .msg-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 18px; }
  .msg-box p { margin: 0; font-size: 15px; color: #374151; line-height: 1.7; white-space: pre-wrap; }
  .foot { padding: 16px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <h2>New Contact Message</h2>
    <p>ICCR Alumni Network — Contact Form</p>
  </div>
  <div class="body">
    <div class="row">
      <div class="label">From</div>
      <div class="value">{{ $name }} &lt;{{ $email }}&gt;</div>
    </div>
    <div class="row">
      <div class="label">Subject</div>
      <div class="value">{{ $subject }}</div>
    </div>
    <div class="row">
      <div class="label">Message</div>
      <div class="msg-box"><p>{{ $message }}</p></div>
    </div>
  </div>
  <div class="foot">Sent from ICCR contact form &nbsp;·&nbsp; {{ now()->format('d M Y, g:i A') }}</div>
</div>
</body>
</html>