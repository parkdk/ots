<?php

namespace MergeXMLOutputs\Model\Queue\Job;

use Manager\Model\Queue\Job\AbstractQueueJob;
use Manager\Entity\Job;

/**
 * Merges meTypeset and CERMINE XML outputs
 */
class MergeJob extends AbstractQueueJob
{
    /**
     * Merges meTypeset and CERMINE XML outputs
     * TODO: finish changing the below
     *
     * @param Job $job
     * @return Job $job
     */
    public function process(Job $job)
    {
        $pdf = $this->sm->get('PdfConversion\Model\Converter\Merge');

        // Fetch the zip file containing the html; check if we got one that has
        // the citations converted first and fall back to unconverted HTML
        if (
            !($document = $job->getStageDocument(JOB_CONVERSION_STAGE_CITATIONSTYLE)) and
            !($document = $job->getStageDocument(JOB_CONVERSION_STAGE_HTML))
        ) {
            throw new \Exception('Couldn\'t find the stage document');
        }

        $outputFile = $job->getDocumentPath() . '/document.pdf';
        $pdf->setInputFile($document->path);
        $pdf->setOutputFile($outputFile);
        $pdf->convert();

        $job->conversionStage = JOB_CONVERSION_STAGE_PDF;

        if (!$pdf->getStatus()) {
            $job->status = JOB_STATUS_FAILED;
            return $job;
        }

        $documentDAO = $this->sm->get('Manager\Model\DAO\DocumentDAO');
        $pdfDocument = $documentDAO->getInstance();
        $pdfDocument->path = $outputFile;
        $pdfDocument->job = $job;
        $pdfDocument->conversionStage = JOB_CONVERSION_STAGE_PDF;

        $job->documents[] = $pdfDocument;

        return $job;
    }
}
