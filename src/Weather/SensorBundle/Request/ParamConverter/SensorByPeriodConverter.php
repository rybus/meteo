<?php

namespace Weather\SensorBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Weather\SensorBundle\Entity\Sensor;

class SensorByPeriodConverter implements ParamConverterInterface
{
    /** @var ManagerRegistry $registry Manager registry */
    private $registry;

    /**
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }


    /**
     * {@inheritdoc}
     */
    function apply(Request $request, ParamConverter $configuration)
    {
        $sensor = $request->attributes->get('id');
        $startDate = $request->attributes->get('startDate');
        $endDate = $request->attributes->get('endDate');

        if (null === $sensor || null === $startDate || null === $endDate) {
            throw new \InvalidArgumentException('Route attribute is missing');
        }

        $em = $this->registry->getManagerForClass($configuration->getClass());

        /** @var \Weather\SensorBundle\Entity\Repository\SensorRepository $sensorRepository Measure repository */
        $sensorRepository = $em->getRepository($configuration->getClass());

        $sensor = $sensorRepository->find($sensor);

        if (null === $sensor || !($sensor instanceof Sensor)) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $configuration->getClass()));
        }

        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 29);

        $request->attributes->set($configuration->getName(), $sensor);
        $request->attributes->set($configuration->getName(), $startDate);
        $request->attributes->set($configuration->getName(), $endDate);
    }

    /**
     * {@inheritdoc}
     */
    function supports(ParamConverter $configuration)
    {
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        $em = $this->registry->getManagerForClass($configuration->getClass());

        if ('SensorBundle\Entity\Measure' !== $em->getClassMetadata($configuration->getClass())->getName()) {
            return false;
        }

        return true;
    }
}