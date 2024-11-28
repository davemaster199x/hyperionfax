# Fax API Service

A RESTful API service for sending faxes through an Asterisk-based fax system.

## Overview

This API service provides functionality to send faxes by accepting PDF documents encoded in base64 format. It integrates with Asterisk Manager Interface (AMI) to handle the actual fax transmission.

## API Specifications

### Base URL Structure
```
/api/v{version}/{method}
```

### Current Version
- v1.0

### Available Methods
- `SendFax` - Sends a fax to the specified destination

### Authentication

The API uses Basic Authentication. Include your credentials in the Authorization header:
```
Authorization: Basic {base64_encoded_username_password}
```

### SendFax Endpoint

**Endpoint:** `/api/v1.0/SendFax`  
**Method:** POST  
**Content-Type:** application/json

#### Request Body Parameters

```json
{
    "src": "source_number",
    "dst": "destination_number",
    "update_url": "callback_url",
    "data": "base64_encoded_pdf_content"
}
```

- `src`: Source fax number
- `dst`: Destination fax number
- `update_url`: URL for status updates
- `data`: PDF document encoded in base64 format

#### Response Format

Success Response:
```json
{
    "status": true,
    "id": "action_id"
}
```

Error Response:
```json
{
    "status": false,
    "message": "error_description"
}
```

## Configuration

The system requires a configuration file (`config.php`) with the following parameters:

### Server Configuration
```php
$config_server = [
    'paths' => [
        'files' => 'path_to_files_directory'
    ],
    'ami' => [
        'server' => 'asterisk_server_address',
        'port' => 'ami_port',
        'username' => 'ami_username',
        'password' => 'ami_password',
        'Async' => 'async_setting',
        'Channel' => 'channel_setting',
        'Context' => 'context_setting',
        'MaxRetries' => 'max_retries',
        'Priority' => 'priority_setting',
        'RetryTime' => 'retry_time',
        'WaitTime' => 'wait_time'
    ]
];
```

## Error Handling

The API returns specific error messages for various scenarios:

- Invalid API version format
- Invalid method call format
- Authentication failures
- Invalid request methods (non-POST requests)
- General processing errors

## File Structure

```
├── config.php
├── classes.d/
│   └── API/
│       └── v1_0.php
└── FAX/
    └── SendFax.php
```

## Dependencies

- PHP 7.0 or higher
- Asterisk with AMI configured
- Base64 encoding/decoding capabilities
- JSON support

## Security Considerations

- The API only accepts POST requests
- Authentication is required for all API calls
- Sensitive configuration data should be properly secured
- All input data is validated before processing

## Notes

- Files are stored with the naming convention: `YYYY-MM-DDfax{id}-data.pdf`
- The system integrates with Asterisk for actual fax transmission
- Status updates can be configured through the update_url parameter

## Error Codes and Messages

Common error responses include:
- "Improper API version format specified"
- "Improper API method call format specified"
- "Invalid request method. Only POST requests are allowed"
- "Incorrect username or password"
- "Something went wrong!"

## Future Enhancements

- Implementation of fax status checking functionality (currently commented in the code)
- Additional error handling and logging
- Extended API documentation
- More detailed status updates

## Support

For additional support or questions, please contact the system administrator.

---

**Note:** This documentation is based on the provided code implementation. Ensure to update configuration values and security measures according to your specific deployment environment.