# Aether Filesystem

REST API for interacting with filesystems.

## API Reference

All directory and file paths are base64 encoded.

## Status

Get status information for filesystem API.

`GET /`

### Returns

| Parameter | Value    | Description |
| ----------| -------- | ----------- |
| `message` | `string` | 'Hello'     |
| `version` | `string` | API version |

## List directory

Get a directory listing for path.

`GET /{path}`

Where:

* `path` => Base64 encoded directory path.

### Parameters

| Parameter   | Value     | Description                    |
| ----------- | --------- | ------------------------------ |
| `recursive` | `boolean` | List sub-directories and files |

### Returns

| Parameter | Value    | Description       |
| ----------| -------- | ----------------- |
| `listing` | `array`  | Directory listing |

## Read file

Get a file at path.

`GET /{path}`

Where:

* `path` => Base64 encoded directory path.
