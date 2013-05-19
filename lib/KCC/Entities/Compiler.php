<?php

namespace KCC\Entities;

use Psc\Doctrine\Annotation;
use Psc\Doctrine\EntityRelation;
use Closure;

class Compiler extends \Psc\CMS\CommonProjectCompiler {

  public function compileProduct() {
    extract($this->help());
    
    return $this->getModelCompiler()->compile(
      $entity('Product'),
      $defaultId(),

      $property('label', $type('String')),
      $property('manufacturer', $type('String'), $nullable()),
      $property('reference', $type('Float')),
      $property('unit', $type('String')),
      $property('kcal', $type('PositiveInteger')),
      
      $property('created', $type('DateTime')),
      $property('updated', $type('DateTime'), $nullable()),
      //$property('active', $type('Boolean'))->setDefaultValue(TRUE),
      
      $constructor(
        $argument('label'),
        $argument('manufacturer'),
        $argument('reference'),
        $argument('unit'),
        $argument('kcal')
      )
      
      /*
      $build($relation($targetMeta('Comment'), 'ManyToMany', 'unidirectional', 'source')), // damit im comment nicht die news_id steht
      $build($relation('Hagedorn\Entities\ContentStream\ContentStream', 'ManyToMany', 'unidirectional', 'source'))
      */
    );
  }

  public function compileCountedProduct() {
    extract($this->help());
    
    return $this->getModelCompiler()->compile(
      $entity('CountedProduct'),
      $defaultId(),

      $property('amount', $type('Float')),
      $property('day', $type('Date')),
      $property('sort', $type('PositiveInteger'), $nullable()),

      $build(
        $relation($targetMeta('Product'), 'ManyToOne', 'unidirectional', 'source')
          ->onDelete(EntityRelation::CASCADE)
      ),
      $build(
        $relation($targetMeta('User')->setIdentifier('email'), 'ManyToOne', 'unidirectional', 'source')
          ->onDelete(EntityRelation::CASCADE)
      ),

      $constructor(
        $argument('product'),
        $argument('amount'),
        $argument('day'),
        $argument('user'),
        $argument('sort', NULL)
      )
    );
  }

  public function compileUser() {
    return $this->doCompileUser();
  }
  
  /*
  public function compileCSTemplateWidgets() {
    return $this->doCompileCSWidgetsDir(
      $this->getWidgetsDir()
    );
  }


  protected function getWidgetsDir() {
    return $this->getPackage()->getRootDirectory()->sub('application/js-src/SCE/Widgets/');
  }

  protected function getPackage() {
    return $GLOBALS['env']['container']->webforge->getLocalPackage();
  }
  */  
}