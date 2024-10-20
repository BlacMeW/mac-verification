Register Request:

POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}


Login Request:

POST /api/login
{
  "email": "john@example.com",
  "password": "password123"
}


Verify TOTP Request:
POST /api/verify-totp
{
  "email": "john@example.com",
  "totp_code": "123456"  // Google Authenticator ကထုတ်တဲ့ 6-digit code
}

