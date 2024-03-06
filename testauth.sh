#!/bin/bash

# Check if the password is provided as a command-line argument
if [[ $# -eq 0 ]]; then
    echo "Usage: testauth.sh <account> <email> <password>"
    exit 1
fi

# Set the base URL of your API
base_url="https://www.500prints.com/api"

# Set the login credentials
account=$1
email=$2
password=$3
apikey="Test-APIKEY"
weblog="bloggability"



# Clear out any existing tokens
echo "----------"
echo "Revoking existing tokens"
./token.php revoke $account
echo " "



# Login request
echo "----------"
echo "Logging in..."
login_response=$(curl -s "$base_url/login" -G \
    --data-urlencode "apikey=$apikey" \
    --data-urlencode "email=$email" \
    --data-urlencode "password=$password" \
    --data-urlencode "weblog=$weblog")

# Extract the status and JWT from the login response
status=$(echo "$login_response" | jq -r '.Status')
jwt=$(echo "$login_response" | jq -r '.jwt')

# Check if the JWT is present
if [[ -z "$jwt" ]]; then
    echo "Failed to retrieve JWT from the login response."
    exit 1
fi

echo "Login response:" $status
echo "JWT:" $jwt
echo " "



# Show token
./token.php list $account

# Renew request
echo "Renewing JWT..."
renew_response=$(curl -s "$base_url/renew" \
    -H "Authorization: Bearer $jwt")

echo "Renew response:"
echo "$renew_response"
echo ""

# Extract the new JWT from the renew response
new_jwt=$(echo "$renew_response" | jq -r '.jwt')

# Check if the new JWT is present
if [[ -z "$new_jwt" ]]; then
    echo "Failed to retrieve new JWT from the renew response."
    exit 1
fi

echo "JWT renewed successfully."
