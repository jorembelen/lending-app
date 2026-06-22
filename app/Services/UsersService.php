<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UsersService {

    public function createAvatar($name)
    {
        try {
            $storagePath = 'uploads/avatar/';
            $fontsPath = public_path('assets/fonts/Arial.ttf');
    
            $initials = $this->getAvatarLetter($name);
            $bgColor = '#' . substr(md5($name), 0, 6);
            $textColor = '#ffffff';
    
            // Create the image with Intervention Image
            $canvas = Image::canvas(200, 200, $bgColor);
            $canvas->text($initials, 100, 100, function ($font) use ($fontsPath, $textColor) {
                $font->file($fontsPath);
                $font->size(75);
                $font->color($textColor);
                $font->align('center');
                $font->valign('middle');
            });
    
            // Generate the file name
            $fileName = str_replace(' ', '-', strtolower($name) . '-avatar.png');
    
            // Stream and save to S3
            $canvas->stream(); // Convert the image to a stream
            Storage::disk('public')->put($storagePath . $fileName, $canvas->__toString());
    
            return $fileName;
    
        } catch (\Exception $e) {
            $msg = 'Error creating avatar: ' . $e->getMessage();
            activity()->withProperties(['attributes' => ['name' => 'error from avatar create']])->log($msg);
            return null;
        }
    }

    protected function getAvatarLetter($name) {
        // $words = explode(' ', $name);
        $nameParts = preg_split('/[\.\_\-]/', $name);
        // Initialize an empty string for the initials
        $initials = '';
    
        // Get the first letter of the first word (first name)
        // $initials .= strtoupper(substr($words[0], 0, 1));
        foreach ($nameParts as $initial) {
            $initials .= strtoupper($initial[0]);
        }
    
        return $initials;
    }

    private function checkFileName($name) 
    {
        $counter = 1;
        
        $originalNameWithoutExtension = pathinfo($name, PATHINFO_FILENAME);
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        if (preg_match('/_(\d+)$/', $originalNameWithoutExtension, $matches)) {
            $baseName = preg_replace('/_\d+$/', '', $originalNameWithoutExtension);
            $counter = (int)$matches[1] + 1;
        } else {
            $baseName = $originalNameWithoutExtension;
        }

        $data = [
            'baseName' => $baseName,
            'extension' => $extension,
            'counter' => $counter,
        ];

        return $data;
        
    }

}