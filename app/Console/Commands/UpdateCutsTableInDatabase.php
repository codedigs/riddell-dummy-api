<?php

namespace App\Console\Commands;

use App\Api\Qx7\CutApi;
use App\Models\Cut;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Log;

class UpdateCutsTableInDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:cuts_table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cuts table in database';

    /**
     * @var CutApi
     */
    protected $cutApi;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->cutApi = new CutApi;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "This will take some time, please wait." . PHP_EOL;
        echo "Fetching data ..." . PHP_EOL;

        $cutResult = $this->cutApi->getAllByBrand();

        if (!is_null($cutResult))
        {
            if ($cutResult->success)
            {
                echo "Updating cuts table process ..." . PHP_EOL;
                Log::info("Updating cuts table process ...");

                $data = $cutResult->lookup_to_styles;

                $cuts = Cut::all();
                $cut_ids = array_column($data, "cut_id");

                foreach ($cuts as $cut)
                {
                    // update if cut on db existing in data
                    if (in_array($cut->cut_id, $cut_ids))
                    {
                        $cutSearched = searchForCutId($cut->cut_id, $data);

                        if (!is_null($cutSearched))
                        {
                            Cut::where('cut_id', $cut->cut_id)
                                ->update([
                                    'cut_id' => $cutSearched['cut_id'],
                                    'hybris_sku' => json_encode($cutSearched['hybris_sku']),
                                    'style_category' => $cutSearched['style_category'],
                                    'gender' => json_encode($cutSearched['gender']),
                                    'name' => $cutSearched['cutInfo']['name'],
                                    'image' => $cutSearched['cutInfo']['image'],
                                    'sport' => $cutSearched['cutInfo']['sport']
                                ]);

                            unset($cut_ids[array_search($cut->cut_id, $cut_ids)]);
                        }
                    }
                    else // otherwise delete it
                    {
                        $cut->delete();
                    }
                }

                // add new cut if any
                foreach ($cut_ids as $cut_id)
                {
                    $cutSearched = searchForCutId($cut_id, $data);

                    if (!is_null($cutSearched))
                    {
                        Cut::create([
                            'cut_id' => $cutSearched['cut_id'],
                            'hybris_sku' => json_encode($cutSearched['hybris_sku']),
                            'style_category' => $cutSearched['style_category'],
                            'gender' => json_encode($cutSearched['gender']),
                            'name' => $cutSearched['cutInfo']['name'],
                            'image' => $cutSearched['cutInfo']['image'],
                            'sport' => $cutSearched['cutInfo']['sport']
                        ]);
                    }
                }

                echo "done" . PHP_EOL;
                Log::info("done");
            }
        }
    }
}
