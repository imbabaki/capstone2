import requests
import json

# Replace with your Pi IP if needed
url = "http://127.0.0.1:5005/start"

data = {
    "paper_size": "A4",
    "count": 3  # Number of papers to dispense
}

try:
    response = requests.post(url, json=data)
    print("Response from Flask:", response.json())
except Exception as e:
    print("Error:", e)
