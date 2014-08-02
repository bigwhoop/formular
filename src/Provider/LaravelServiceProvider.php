<?php
/**
 * This file is part of Formular.
 *
 * (c) Philippe Gerber
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bigwhoop\Formular\Provider;
use Illuminate\Support\ServiceProvider;

/**
 * @author Philippe Gerber <philippe@bigwhoop.ch>
 */
class LaravelServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected $defer = false;

    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->package('bigwhoop/formular');
    }
    

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app['formular'] = function() {
            return new Bootstrap3Form([
                'csrf_element_name'  => '_token',
                'csrf_element_value' => \Session::token(),
            ]);
        };
    }
    

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [];
    }
}
