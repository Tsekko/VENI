<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Rechercher;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\DateType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function rechercherFiltres(Rechercher $rechercher, Participant $participant) {

        $q = $this
            ->createQueryBuilder('s');


        if (!empty($rechercher->getQuery()))
        {
            $q= $q
                ->andWhere('s.nom LIKE :query')
                ->setParameter('query', "%{$rechercher->getQuery()}%")
                ->andWhere('s.archive = false');
        }

        if(empty($rechercher->getQuery())) {
            $q= $q
                ->andWhere('s.archive = false');
        }

        // Ne fonctionne pas - à revoir
        if($rechercher->getSite() != null) {
            $q = $q
                ->leftjoin('s.organisateur', 'p')
                ->andWhere('p.site = :site')
                ->setParameter('site', $rechercher->getSite());
        }

        if ($rechercher->getDebut() != null && $rechercher->getFin() != null)
        {
            $q= $q
                ->andWhere('s.dateHeureDebut BETWEEN :debut AND :fin')
                ->setParameter('debut', $rechercher->getDebut())
                ->setParameter('fin', $rechercher->getFin())
                ->orderBy('s.dateLimiteInscription', 'DESC');
        }


        if ($rechercher->isCheckboxOrganisateur() != null) {
            $q= $q
                ->andWhere('s.organisateur = :idOrganisateur')
                ->setParameter('idOrganisateur', $participant->getId())
                ->andWhere('s.archive = false');
        }

        if ($rechercher->isCheckboxInscrit() != null) {
            $q= $q
                ->leftjoin('s.participants', 'p')
                ->andWhere('p.nom = :nom')
                ->setParameter('nom', $participant->getNom())
                ->andWhere('s.archive = false');
        }

        // Ne fonctionne pas - à revoir
        if ($rechercher->isCheckboxNonInscrit() != null) {
            $q= $q
                ->join('s.participants', 'p')
                ->andHaving(':userId NOT IN (p.id)')
                ->setParameter('userId', $participant->getId())
                ->groupBy('p.id');
        }

        if ($rechercher->isCheckboxPasses() != null) {
            $q = $q
                ->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime('now'));
        }

        $q = $q
        ->orderBy('s.dateHeureDebut', 'ASC')
        ->getQuery()
        ->getResult();
        return $q;
}


//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
