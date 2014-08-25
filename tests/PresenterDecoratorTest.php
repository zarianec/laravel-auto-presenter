<?php namespace McCool\Tests;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use McCool\LaravelAutoPresenter\Decorators\AtomDecorator;
use McCool\LaravelAutoPresenter\Decorators\CollectionDecorator;
use McCool\LaravelAutoPresenter\Decorators\PaginatorDecorator;
use McCool\LaravelAutoPresenter\PresenterDecorator;
use McCool\Tests\Stubs\DecoratedAtom;
use McCool\Tests\Stubs\UndecoratedAtom;
use McCool\Tests\Stubs\WronglyDecoratedAtom;

use Mockery as m;

class PresenterDecoratorTest extends TestCase
{
	private $decorator;

	public function setUp()
	{
		$this->decorator = new PresenterDecorator(
			new AtomDecorator,
			new CollectionDecorator,
			new PaginatorDecorator
		);
	}

    public function testWontDecorateOtherObjects()
    {
        $atom = new UndecoratedAtom;
        $decoratedAtom = $this->decorator->decorate($atom);

        $this->assertInstanceOf('McCool\Tests\Stubs\UndecoratedAtom', $decoratedAtom);
    }

    public function testDecoratesAtom()
    {
        $atom = $this->getDecoratedAtom();
        $decoratedAtom = $this->decorator->decorate($atom);

        $this->assertInstanceOf('McCool\Tests\Stubs\DecoratedAtomPresenter', $decoratedAtom);
    }

    public function testDecoratesPaginator()
    {
        $paginator = $this->getFilledPaginator();
        $decoratedPaginator = $this->decorator->decorate($paginator);

        foreach ($decoratedPaginator as $decoratedAtom) {
            $this->assertInstanceOf('McCool\Tests\Stubs\DecoratedAtomPresenter', $decoratedAtom);
        }
    }

    public function testDecorateCollection()
    {
        $collection = $this->getFilledCollection();
        $decoratedCollection = $this->decorator->decorate($collection);

        foreach ($decoratedCollection as $decoratedAtom) {
            $this->assertInstanceOf('McCool\Tests\Stubs\DecoratedAtomPresenter', $decoratedAtom);
        }
    }

    /**
    * @covers decorator::decorate
    * @expectedException McCool\LaravelAutoPresenter\PresenterNotFoundException
    */
    public function testWronglyDecoratedClassThrowsException()
    {
        $atom = new WronglyDecoratedAtom;

        $this->decorator->decorate($atom);
    }

    private function getDecoratedAtom()
    {
        return new DecoratedAtom;
    }

    private function getFilledPaginator()
    {
        $items = array();

        foreach (range(1, 5) as $i) {
            $items[] = $this->getDecoratedAtom();
        }

        $factory = m::mock('Illuminate\Pagination\Factory');
        $factory->shouldReceive('getCurrentPage')->andReturn(1);

        $paginator = new Paginator(
            $factory,
            $items,
            10,
            5
        );

        return $paginator->setupPaginationContext();
    }

    private function getFilledCollection()
    {
        $items = array();

        foreach (range(1, 5) as $i) {
            $items[] = $this->getDecoratedAtom();
        }

        return new Collection($items);
    }
}