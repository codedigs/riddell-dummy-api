<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    // enable options
    protected function enableOptions($query)
    {
        $sort = $this->request->get('sort');
        $limit = $this->request->get('limit');

        if (!is_null($sort))
        {
            $sort_value = "ASC";
            switch(strtolower($sort))
            {
                case "desc":
                case "descending":
                    $sort_value = "DESC";
                    break;
            }

            $query->orderBy('id', $sort_value);
        }

        if (!is_null($limit) && is_numeric($limit))
        {
            $query->limit($limit);
        }
    }

    /**
     * @param $message
     * @return Response
     */
    protected function respondWithMissingField($message)
    {
        return response()->json([
            'status' => 400,
            'message' => $message,
        ], 400);
    }

    /**
     * @param $message
     * @return Response
     */
    private function respondWithValidationError($message)
    {
        return response()->json([
            'status' => 406,
            'message' => $message,
        ], 406);
    }

    /**
     * @param $validator
     * @return Response
     */
    protected function respondWithErrorMessage($validator)
    {
        $required = $messages = [];
        $validatorMessages = $validator->errors()->toArray();

        foreach($validatorMessages as $field => $message) {
            if (strpos($message[0], 'required')) {
                $required[] = $field;
            }

            foreach ($message as $error) {
                $messages[] = $error;
            }
        }

        if (count($required) > 0) {
            $fields = implode(', ', $required);
            $message = "Missing required fields $fields";

            return $this->respondWithMissingField($message);
        }


        return $this->respondWithValidationError(implode(', ', $messages));
    }
}
