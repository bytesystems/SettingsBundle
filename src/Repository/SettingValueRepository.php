<?php


namespace Bytesystems\SettingsBundle\Repository;


use Bytesystems\SettingsBundle\Entity\Setting;
use Bytesystems\SettingsBundle\Entity\SettingValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SettingValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method SettingValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method SettingValue[]    findAll()
 * @method SettingValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class SettingValueRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SettingValue::class);
    }


    public function findValueEntry(string $key, ?string $ownerKey = null)
    {
        $qb = $this->createQueryBuilder('sv');

        $qb->andWhere($qb->expr()->eq('IDENTITY(sv.setting)',':key'))->setParameter('key',$key);

        if(null === $ownerKey)
        {
            $qb->andWhere($qb->expr()->isNull('sv.owner'));
        }
        else
        {
            $qb->andWhere($qb->expr()->eq('sv.owner',':owner'))->setParameter('owner',$ownerKey);
        }
        return $qb->getQuery()->getOneOrNullResult();
    }
}