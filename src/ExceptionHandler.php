<?php


namespace Job;


use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler as DebugException;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class ExceptionHandler implements DebugException
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Report or log an exception.
     *
     * @param \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        $this->app['logger']->error($e->getMessage());
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param \Exception $e
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return true;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $e
     * @return Response
     */
    public function render($request, Exception $e)
    {
        return new Response;
    }

    /**
     * Render an exception to the console.
     *
     * @param OutputInterface $output
     * @param Exception $e
     * @return void
     */
    public function renderForConsole($output, Exception $e)
    {
        $output->write($e->getMessage());
    }
}