<?php


namespace Job;


use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler as DebugException;

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
     * @param \Illuminate\Http\Request $request
     * @param \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {

    }

    /**
     * Render an exception to the console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception $e
     * @return void
     */
    public function renderForConsole($output, Exception $e)
    {
        $output->write($e->getMessage());
    }
}