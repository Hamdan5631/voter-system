# S3 Bucket Configuration Guide

## Overview

The Digital Voters List API supports both local storage and Amazon S3 for voter images. By default, images are stored locally. To use S3, you need to configure your AWS credentials and settings.

## Prerequisites

1. AWS Account with S3 access
2. An S3 bucket created
3. AWS Access Key ID and Secret Access Key
4. Proper IAM permissions for the bucket

## Installation

### 1. Install AWS SDK Package

The required package `league/flysystem-aws-s3-v3` should already be in `composer.json`. If not, install it:

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### 2. Configure Environment Variables

Add the following to your `.env` file:

```env
# S3 Configuration
VOTER_IMAGE_DISK=s3

# AWS Credentials
AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# Optional: Custom S3 endpoint (for S3-compatible services like DigitalOcean Spaces)
AWS_ENDPOINT=
AWS_URL=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### 3. Bucket Setup

#### Create S3 Bucket

1. Go to AWS S3 Console
2. Create a new bucket
3. Set bucket name (e.g., `voters-list-images`)
4. Choose region (note the region code for `AWS_DEFAULT_REGION`)

#### Configure Bucket Permissions

Your S3 bucket needs to allow:
- **PutObject** - To upload images
- **GetObject** - To retrieve images
- **DeleteObject** - To delete images

#### Bucket Policy Example (Public Read Access)

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::your-bucket-name/voters/*"
        }
    ]
}
```

#### CORS Configuration (if needed)

If accessing images from a web application, configure CORS:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "HEAD"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": []
    }
]
```

### 4. IAM User Permissions

Create an IAM user with the following policy:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:GetObject",
                "s3:DeleteObject",
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::your-bucket-name",
                "arn:aws:s3:::your-bucket-name/*"
            ]
        }
    ]
}
```

## Switching Between Local and S3

### Use Local Storage (Default)

```env
VOTER_IMAGE_DISK=public
# or simply don't set VOTER_IMAGE_DISK
```

### Use S3 Storage

```env
VOTER_IMAGE_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

## Image URL Format

### Local Storage
```
http://localhost:8000/storage/voters/1234567890_abc123.jpg
```

### S3 Storage
```
https://your-bucket-name.s3.amazonaws.com/voters/1234567890_abc123.jpg
```

The API automatically returns the correct URL format based on your configuration. The `image_url` attribute is automatically included in voter responses.

## Testing S3 Configuration

### Test Upload

1. Make sure `.env` has S3 credentials configured
2. Set `VOTER_IMAGE_DISK=s3`
3. Create a voter with an image via API
4. Check your S3 bucket to verify the file was uploaded

### Test URL Generation

```php
$voter = Voter::first();
echo $voter->image_url; // Returns full S3 URL if configured
```

## Alternative S3-Compatible Services

The configuration also works with S3-compatible services:

### DigitalOcean Spaces

```env
VOTER_IMAGE_DISK=s3
AWS_ACCESS_KEY_ID=your_spaces_key
AWS_SECRET_ACCESS_KEY=your_spaces_secret
AWS_DEFAULT_REGION=nyc3
AWS_BUCKET=your-space-name
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### MinIO

```env
VOTER_IMAGE_DISK=s3
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_ENDPOINT=http://localhost:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### Wasabi

```env
VOTER_IMAGE_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_ENDPOINT=https://s3.wasabisys.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## Troubleshooting

### Common Issues

1. **403 Forbidden Error**
   - Check IAM user permissions
   - Verify bucket policy allows operations
   - Ensure credentials are correct

2. **Images Not Uploading**
   - Verify `VOTER_IMAGE_DISK=s3` in `.env`
   - Check AWS credentials
   - Ensure bucket name is correct
   - Check region matches bucket region

3. **URL Not Working**
   - Verify bucket has public read access (if needed)
   - Check CORS configuration
   - Ensure `image_url` accessor is being used

4. **Migration from Local to S3**
   - Images already uploaded to local storage remain local
   - Only new uploads will use S3
   - Consider migrating existing images if needed

## Migration Script (Optional)

If you need to migrate existing images from local to S3:

```php
// Run this in tinker: php artisan tinker
use App\Models\Voter;
use Illuminate\Support\Facades\Storage;

$voters = Voter::whereNotNull('image_path')->get();

foreach ($voters as $voter) {
    if (Storage::disk('public')->exists($voter->image_path)) {
        $contents = Storage::disk('public')->get($voter->image_path);
        Storage::disk('s3')->put($voter->image_path, $contents, 'public');
        echo "Migrated: {$voter->serial_number}\n";
    }
}
```

## Security Best Practices

1. **Never commit AWS credentials to version control**
   - Use `.env` file (already in `.gitignore`)
   - Use environment variables in production

2. **Use IAM Roles in Production**
   - For EC2/ECS deployments, use IAM roles instead of access keys
   - More secure than hardcoded credentials

3. **Restrict Bucket Access**
   - Only allow necessary IP ranges if possible
   - Use bucket policies to limit access

4. **Enable Versioning (Optional)**
   - Useful for audit trails
   - Can recover deleted images

5. **Enable Lifecycle Policies**
   - Automatically delete old images if needed
   - Reduce storage costs

## Cost Optimization

1. **Use S3 Intelligent-Tiering**
   - Automatically moves objects to the most cost-effective tier

2. **Set Lifecycle Rules**
   - Delete images older than X days
   - Move to Glacier for long-term storage

3. **Enable Compression**
   - Images are already optimized during upload (800x800 max)
   - Consider additional compression if needed
