# /login

{% api-method method="post" host="https://localhost:8000" path="/v1/api/auth/login" %}
{% api-method-summary %}
Login
{% endapi-method-summary %}

{% api-method-description %}
This endpoint validates credentials and authorize user to service
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-body-parameters %}
{% api-method-parameter name="email" type="string" required=true %}
user validated email
{% endapi-method-parameter %}

{% api-method-parameter name="password" type="string" required=true %}
user password
{% endapi-method-parameter %}
{% endapi-method-body-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```
{
    "id": "62f096ee-08d6-11ec-b09d-1c1b0da97ebc",
    "email": "test@gmail.com",
    "roles": [
        "ROLE_USER"
    ],
    "profile_pic": "https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png"
} 
+ BEARER and REFRESH_TOKEN cookies
```
{% endapi-method-response-example %}

{% api-method-response-example httpCode=401 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```
{
    "code": 401,
    "message": "Invalid credentials."
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}



