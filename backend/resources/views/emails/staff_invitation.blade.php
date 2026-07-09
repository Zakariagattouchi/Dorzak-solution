<!DOCTYPE html>
<html>
<body style="font-family: system-ui, sans-serif; color: #1a1c1c;">
    <h2>You've been invited to {{ $store }}</h2>
    <p>You've been invited to join <strong>{{ $store }}</strong> on Dorzak Merchant as a <strong>{{ $role }}</strong>.</p>
    <p>
        <a href="{{ $acceptUrl }}"
           style="display:inline-block;padding:10px 18px;background:#1890ff;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
            Accept invitation
        </a>
    </p>
    <p style="color:#6b7280;font-size:0.85rem;">This invitation expires in 7 days. If you weren't expecting it, you can ignore this email.</p>
</body>
</html>
