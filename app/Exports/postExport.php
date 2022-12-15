<?php

namespace App\Exports;

use App\PostProblem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class postExport implements FromView,ShouldAutoSize 
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     //
    // }
    protected $id;

        function __construct($id) {
                $this->id = $id;
        }
        public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                foreach ($event->sheet->getColumnIterator('G') as $row) {
                    
                    foreach ($row->getCellIterator() as $cell) {
                        if ($cell->getValue() != "" && str_contains($cell->getValue(), '://')) {
                            $cell->setHyperlink(new Hyperlink($cell->getValue(), 'Read'));

                            // Upd: Link styling added
                            $event->sheet->getStyle($cell->getCoordinate())->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => '0000FF'],
                                    'underline' => 'single'
                                ]
                            ]);
                        }
                    }
                }
            },
        ];
    }
    public function view(): View
    { 
       
        $postdata = PostProblem::select('*')->with('languagePost')->where('id', $this->id)->first();
        foreach($postdata->getSolutionCount as $solution){
            //  dd($solution);
        }
        //return view('exports.binexport', compact('driverarr'));
        return view('exports.postexport', [
           'postdata' => $postdata
        ]);
    }
    

    
   
}
