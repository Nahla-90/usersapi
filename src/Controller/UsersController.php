<?php

namespace App\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Class UsersController
 * @package App\Controller
 */
class UsersController extends FOSRestController
{
    /**
     * Created By Nahla Sameh
     * Get filtered users
     * @GET(
     *     "/api/v1/users",
     *      name="users",
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function usersAction(Request $request): JsonResponse
    {
        try {
            /* Prepare Data Provider List to check according to request params*/
            $dataProvidersList = $this->_prepareDataProvidersList($request);

            /* Prepare Filter List according to request filter params*/
            $filters = $this->_prepareFilters($request);

            /* Filter Users according to providers and filter params*/
            $filteredUsers = array();

            /* Loop On DataProviders */
            foreach ($dataProvidersList as $dataProviderFilename) {
                /* Read Data provider content from Data Provider file */
                $dataProviderContent = file_get_contents($this->container->getParameter('application.data_provider_path') . '/' . $dataProviderFilename);
                $dataProviderUsersJson = json_decode($dataProviderContent);

                /* Get users of data provider */
                $users = (!empty($dataProviderUsersJson->users)) ? $dataProviderUsersJson->users : array();
                /* Get statusList of data provider */
                $statusList = (!empty($dataProviderUsersJson->status)) ? $dataProviderUsersJson->status : array();

                /* Check if there is filter should be applied in $filters list*/
                if (!empty($filters)) {

                    /* Loop on users to check if it apply filter conditions or not*/
                    foreach ($users as $user) {
                        if ($this->_checkUserIfApplyFilters($filters, $statusList, $user)) {

                            /* If user apply filter conditions then it will be added to filtered users */
                            $filteredUsers[] = $user;
                        }
                    }
                } else {
                    /* If No filters found, then add all users to filtered users list*/
                    $filteredUsers = array_merge($filteredUsers, $users);
                }
            }

            /* Return json response with filtered users list*/
            return new JsonResponse(array('success' => true, 'users' => $filteredUsers), 200);
        } catch (\Exception $e) {

            /** prepare error response array */
            return new JsonResponse(array('success' => false, 'message' => $e->getMessage()), 200);
        }
    }

    /**
     * Created By Nahla Sameh
     * Check Allowed providers to filter according to request params
     * @param Request $request
     * @return array
     */
    function _prepareDataProvidersList(Request $request)
    {
        /* Get Provider if exist on get params*/
        $dataProviderName = $request->get('provider', null);

        /* Init data providers list to fill */
        $dataProvidersList = array();

        /* Check If request has specific data provider */
        if ($dataProviderName !== null) {

            /* Validate Requested Data provide value */
            $dataProviderValid = $this->_validate($dataProviderName, [new NotBlank(), new Type('string'),new Regex('/^[a-z]+$/i')]);

            if ($dataProviderValid['valid']) { /* If requested Data provider is valid */

                /* Check If Provider Exist */
                if (file_exists($this->container->getParameter('application.data_provider_path') . '/' . $dataProviderName . '.json')) {

                    /* If Provider Exist, then Add To DataProviderList to get users from */
                    $dataProvidersList[] = $dataProviderName . '.json';
                }else{

                    /* If Provider not Exist, then throw exception */
                    throw new Exception('Invalid Data Provider Name');
                }
            } else {

                /* If ProviderName not Valid, then throw exception */
                throw new Exception('Data Provider '.$dataProviderValid['message']);
            }
        } else {
            /* check all data providers to add to $providersList */
            $files = scandir($this->container->getParameter('application.data_provider_path'));
            foreach ($files as $file) {

                /* Check if it is Data Provider file */
                if (strpos($file, 'DataProvider') !== false) {
                    $dataProvidersList[] = $file;
                }
            }
        }

        /* Return Data Providers List */
        return $dataProvidersList;
    }

    /**
     * Created By Nahla Sameh
     * Prepare filters according to request filter params
     * @param Request $request
     * @return mixed
     */
    function _prepareFilters(Request $request)
    {
        $filters = array();

        /* Check If currency filter exist on request params */
        $currencyFilter = $request->get('currency', null);
        if ($currencyFilter !== null) {

            /* Validate requested currency filter value using (String, NotBlank, alphabetic Only) */
            $currencyValid = $this->_validate($currencyFilter, [new NotBlank(), new Type('string'),new Regex('/^[a-z]+$/i')]);
            if ($currencyValid['valid']) {

                /* If requested currency filter is Valid, then add to filters list according to fieldSlug of each DataProvider*/
                $filters['currency'] = array('equalString' => $currencyFilter, null);
                $filters['Currency'] = array('equalString' => $currencyFilter, null);
            } else {

                /* If requested currency filter is not Valid, then throw exception with the validation error */
                throw new Exception('Currency '.$currencyValid['message']);
            }
        }

        /* Check If statusCode filter exist on request params */
        $statusCodeFilter = $request->get('statusCode', null);
        if ($statusCodeFilter !== null) {

            /* Validate requested Status Code filter value using (String, NotBlank, alphabeticOnly) */
            $statusCodeValid = $this->_validate($statusCodeFilter, [new NotBlank(), new Type('string'),new Regex('/^[a-z]+$/i')]);
            if ($statusCodeValid['valid']) {

                /* If requested Status Code filter is Valid, then add to filters list according to fieldSlug of each DataProvider*/
                $filters['status'] = array('equal' => $statusCodeFilter);
                $filters['statusCode'] = array('equal' => $statusCodeFilter);
            } else {

                /* If requested Status Code filter is not Valid, then throw exception with the validation error */
                throw new Exception('Status Code '.$statusCodeValid['message']);
            }
        }

        /* Check If balanceMin or balanceMax filter exist on request params */
        $balanceMinFilter = $request->get('balanceMin', null);
        $balanceMaxFilter = $request->get('balanceMax', null);
        if ($balanceMinFilter !== null || $balanceMaxFilter !== null) {

            /* Init filters with empty arrays */
            $filters['balance'] = array();
            $filters['parentAmount'] = array();

            /* Init validation constraint required for request balance values*/
            $balanceConstraint = array(
                new NotBlank(),
                new Type('float'),
                new PositiveOrZero()
            );


            /* Check If balanceMin filter exist on request params */
            if ($balanceMinFilter !== null) {

                /* convert balanceMin to float, so i can use it in float validation */
                $balanceMinFilter = is_numeric($balanceMinFilter) ? (float)$balanceMinFilter : $balanceMinFilter;

                /* Validate requested balanceMin filter value using $balanceConstraint array*/
                $balanceMinValid = $this->_validate($balanceMinFilter, $balanceConstraint);
                if ($balanceMinValid['valid']) {

                    /* If requested balanceMin filter is Valid, then add to filters list according to fieldSlug of each DataProvider*/
                    $filters['balance']['min'] = $balanceMinFilter;
                    $filters['parentAmount']['min'] = $balanceMinFilter;
                } else {

                    /* If requested balanceMin filter is not Valid, then throw exception with the validation error */
                    throw new Exception('balanceMin ' . $balanceMinValid['message']);
                }
            }

            /* Check If balanceMax filter exist on request params */
            if ($balanceMaxFilter !== null) {

                /* convert balanceMax to float, so i can use it in float validation */
                $balanceMaxFilter = is_numeric($balanceMaxFilter) ? (float)$balanceMaxFilter : $balanceMaxFilter;

                /* Validate requested balanceMax filter value using $balanceConstraint array*/
                $balanceMaxValid = $this->_validate($balanceMaxFilter, $balanceConstraint);
                if ($balanceMaxValid['valid']) {

                    /* If requested balanceMax filter is Valid, then check if it greater than balanceMin if balanceMin filter exist*/
                    if ($balanceMinFilter !== null && $balanceMaxFilter < $balanceMinFilter) {
                        throw new Exception('balanceMax Should be greater than balanceMin');
                    }

                    /* add to filters list according to fieldSlug of each DataProvider*/
                    $filters['balance']['max'] = $balanceMaxFilter;
                    $filters['parentAmount']['max'] = $balanceMaxFilter;
                } else {

                    /* If requested balanceMax filter is not Valid, then throw exception with the validation error */
                    throw new Exception('balanceMax ' . $balanceMaxValid['message']);
                }
            }
        }

        /* Return filters */
        return $filters;
    }

    /**
     * Created By Nahla Sameh
     * Check If User apply filters requested or not
     * @param $allowedFiltersParams
     * @param $statusList
     * @param $user
     * @return bool
     */
    function _checkUserIfApplyFilters($allowedFiltersParams, $statusList, $user)
    {
        /* Loop on user fields to check if it apply filters*/
        foreach ($user as $key => $value) {

            /* If current field not status or status code */
            if ($key !== 'status' && $key !== 'statusCode') {

                /* If filter method is equalString, and value not equal required value of filter, then return false */
                if (!empty($allowedFiltersParams[$key]) && isset($allowedFiltersParams[$key]['equalString']) && strtolower($value) !== strtolower($allowedFiltersParams[$key]['equalString'])) {
                    return false; /* User not apply filters values */
                }

                /* If filter method is equal, and value not equal required value of filter, then return false */
                if (!empty($allowedFiltersParams[$key]) && isset($allowedFiltersParams[$key]['equal']) && $value !== $allowedFiltersParams[$key]['equal']) {
                    return false;/* User not apply filters values */
                }

                /* If filter method is min, and value less than required value of filter, then return false */
                if (!empty($allowedFiltersParams[$key]) && isset($allowedFiltersParams[$key]['min']) && $value < $allowedFiltersParams[$key]['min']) {
                    return false;/* User not apply filters values */
                }

                /* If filter method is max, and value more than required value of filter, then return false */
                if (!empty($allowedFiltersParams[$key]) && isset($allowedFiltersParams[$key]['max']) && $value > $allowedFiltersParams[$key]['max']) {
                    return false;/* User not apply filters values */
                }
            } else { /* If current field is status or status code */

                /* Check if status&statusCode fields exist on filters */
                if (!empty($allowedFiltersParams[$key])) {

                    /* Check if status&statusCode filter value exist on StatusList of the current DataProvider */
                    if (!empty($statusList->{$allowedFiltersParams[$key]['equal']})) {

                        /* If Exist, Then Gets its int Value*/
                        $statusIntValue = $statusList->{$allowedFiltersParams[$key]['equal']};

                        /* Check current status of user if it equal to requested status value*/
                        if (!empty($allowedFiltersParams[$key]['equal']) && $value !== $statusIntValue) {
                            return false;/* User not apply filters values */
                        }

                    } else {
                        /* if status&statusCode filter value not exist on StatusList of the current DataProvider, then throw exception */
                        throw new Exception('Status Code Not Found');
                    }
                }
            }
        }

        /* User apply filters values */
        return true;
    }

    /**
     * Created By Nahla Sameh
     * validate value using custom validation constraints
     * @param $value
     * @param $constraints
     * @return array
     */
    function _validate($value, $constraints)
    {
        /* Create validator object to use */
        $validator = Validation::createValidator();

        /* Validate value using constraints*/
        $violations = $validator->validate($value, $constraints);

        /* If violations exist */
        if (count($violations) !== 0) {
            $violationMsg = '';
            foreach ($violations as $key => $violation) {
                if ($key > 0) {
                    $violationMsg .= ', ';
                }
                $violationMsg .= $violation->getMessage();
            }
            /* return violation msgs in string format*/
            return array('valid' => false, 'message' => $violationMsg);
        }

        /* return that value is valid */
        return array('valid' => true, 'message' => 'valid value');
    }
}
