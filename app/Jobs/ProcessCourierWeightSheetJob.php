<?php

namespace App\Jobs;

use App\Models\CourierWeightUpload;
use App\Imports\CourierWeightImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ProcessCourierWeightSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $uploadId;

    public function __construct(int $uploadId)
    {
        $this->uploadId = $uploadId;
    }


    public function handle(): void
    {

        $upload = CourierWeightUpload::find($this->uploadId);
        if (!$upload) {
            return;
        }

        $upload->update(['status' => 'processing']);

        Excel::import(
            new CourierWeightImport,
            $upload->file_path,
            'local' // ✅ IMPORTANT
        );

        $upload->update(['status' => 'completed']);
    }

}
