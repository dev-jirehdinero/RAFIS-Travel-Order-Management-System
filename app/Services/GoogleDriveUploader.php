<?php

namespace App\Services;

use App\Exceptions\GoogleDriveUploadException;
use App\Models\TravelOrder;
use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Replaces the Make.com scenario: uploads a file straight into the shared
 * Google Drive folder, named "[TO Number] - [File Name]", and returns its link.
 */
class GoogleDriveUploader
{
    /**
     * @return string The web view link of the uploaded file.
     *
     * @throws GoogleDriveUploadException
     */
    public function upload(UploadedFile $file, TravelOrder $order): string
    {
        $folderId = config('google_drive.folder_id');
        if (! $folderId) {
            throw new GoogleDriveUploadException('Google Drive is not configured. Please contact the administrator.');
        }

        $name = $this->targetName($order, $file);

        try {
            $drive = $this->drive();

            $created = $drive->files->create(
                new DriveFile(['name' => $name, 'parents' => [$folderId]]),
                [
                    'data' => $file->get(),
                    'mimeType' => $file->getMimeType() ?: 'application/octet-stream',
                    'uploadType' => 'multipart',
                    'fields' => 'id,webViewLink',
                    'supportsAllDrives' => true,
                ]
            );
        } catch (GoogleDriveUploadException $e) {
            throw $e;
        } catch (ConnectException $e) {
            Log::warning('Google Drive connection failed', ['order' => $order->assignment_id, 'error' => $e->getMessage()]);
            throw new GoogleDriveUploadException('Could not reach Google Drive (connection timed out). Please try again.', 0, $e);
        } catch (GoogleServiceException $e) {
            Log::error('Google Drive API error', ['order' => $order->assignment_id, 'code' => $e->getCode(), 'error' => $e->getMessage()]);
            $message = in_array($e->getCode(), [401, 403], true)
                ? 'Google Drive rejected the credentials or folder permissions. Please contact the administrator.'
                : 'Google Drive returned an error while uploading. Please try again.';
            throw new GoogleDriveUploadException($message, 0, $e);
        } catch (GuzzleException $e) {
            Log::error('Google Drive transport error', ['order' => $order->assignment_id, 'error' => $e->getMessage()]);
            throw new GoogleDriveUploadException('A network error occurred while uploading. Please try again.', 0, $e);
        }

        $link = $created->getWebViewLink();
        if (! $link) {
            throw new GoogleDriveUploadException('The file was uploaded but Google Drive returned no link.');
        }

        return $link;
    }

    /** "[TO Number] - [File Name]", with characters Drive/OSes dislike stripped. */
    public function targetName(TravelOrder $order, UploadedFile $file): string
    {
        $original = basename($file->getClientOriginalName());
        $clean = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]+/', '_', $original);

        return $order->assignment_id.' - '.$clean;
    }

    private function drive(): Drive
    {
        $client = new GoogleClient();
        $client->setClientId(config('google_drive.client_id'));
        $client->setClientSecret(config('google_drive.client_secret'));
        $client->setHttpClient(new GuzzleClient([
            'connect_timeout' => config('google_drive.connect_timeout'),
            'timeout' => config('google_drive.timeout'),
        ]));

        $token = $client->fetchAccessTokenWithRefreshToken(config('google_drive.refresh_token'));
        if (isset($token['error'])) {
            Log::error('Google Drive token refresh failed', ['error' => $token['error']]);
            throw new GoogleDriveUploadException('Google Drive credentials are invalid or expired. Please contact the administrator.');
        }

        return new Drive($client);
    }
}
