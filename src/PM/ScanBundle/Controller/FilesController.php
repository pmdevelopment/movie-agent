<?php

namespace PM\ScanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class FilesController
 *
 * @package PM\ScanBundle\Controller
 *
 * @Route("/scan")
 */
class FilesController extends Controller
{
    /**
     * Index
     *
     * @param int $folder
     *
     * @return array
     *
     * @Route("/files")
     * @Route("/files/{folder}")
     * @Template()
     */
    public function indexAction($folder = 0)
    {
        if (0 === $folder) {
            $parent = null;
            $files = array();
        } else {
            $parent = $this->getDoctrine()->getRepository("PMScanBundle:Folder")->find(intval($folder));
            $files = $this->getDoctrine()->getRepository("PMScanBundle:File")->findBy(array('folder' => $parent), array("name" => "asc"));
        }

        $folders = $this->getDoctrine()->getRepository("PMScanBundle:Folder")->findBy(array('parent' => $parent), array("name" => "asc"));

        return array(
            'folders' => $folders,
            'files'   => $files,
            'parent'  => $parent
        );
    }
}
