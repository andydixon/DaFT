<?php

namespace Federaliser\Controller;

use Federaliser\Config\ConfigModel;

class ConfigController
{
    private ConfigModel $model;

    public function __construct(ConfigModel $model)
    {
        $this->model = $model;
    }

    /**
     * Dispatch method based on $_GET['action']
     */
    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? 'list';

        switch ($action) {
            case 'create':
                $this->create();
                break;
            case 'store':
                $this->store();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                $this->index();
        }
    }

    /**
     * Show list of all config sections
     */
    private function index(): void
    {
        $sections = $this->model->getAll();
        include __DIR__ . '/../../Views/list.php';
    }

    /**
     * Show create form
     */
    private function create(): void
    {
        include __DIR__ . '/../../Views/create.php';
    }

    /**
     * Handle form submission for creating a new section
     */
    private function store(): void
    {
        $sectionName = $_POST['section_name'] ?? '';
        $data = [
            'hostname'   => $_POST['hostname'] ?? '',
            'port'       => $_POST['port'] ?? '',
            'type'       => $_POST['type'] ?? '',
            'identifier' => $_POST['identifier'] ?? '',
            'username'   => $_POST['username'] ?? '',
            'password'   => $_POST['password'] ?? '',
            'default_db' => $_POST['default_db'] ?? '',
            'query'      => $_POST['query'] ?? '',
        ];

        try {
            $this->model->create($sectionName, $data);
            header('Location: ?action=list');
        } catch (\Exception $e) {
            $error = $e->getMessage();
            include __DIR__ . '/../../Views/create.php';
        }
    }

    /**
     * Show edit form
     */
    private function edit(): void
    {
        $sectionName = $_GET['section'] ?? '';
        $sectionData = $this->model->get($sectionName);

        if (!$sectionData) {
            die("Section not found.");
        }

        include __DIR__ . '/../../Views/edit.php';
    }

    /**
     * Handle form submission for editing a section
     */
    private function update(): void
    {
        $oldSection = $_POST['old_section'] ?? '';
        $newSection = $_POST['section_name'] ?? '';
        $data = [
            'hostname'   => $_POST['hostname'] ?? '',
            'port'       => $_POST['port'] ?? '',
            'type'       => $_POST['type'] ?? '',
            'identifier' => $_POST['identifier'] ?? '',
            'username'   => $_POST['username'] ?? '',
            'password'   => $_POST['password'] ?? '',
            'default_db' => $_POST['default_db'] ?? '',
            'query'      => $_POST['query'] ?? '',
        ];

        try {
            $this->model->update($oldSection, $newSection, $data);
            header('Location: ?action=list');
        } catch (\Exception $e) {
            $error = $e->getMessage();
            // In case of error, we can re-use $data but also pass the original $sectionName so the form stays populated.
            $sectionData = $data;
            include __DIR__ . '/../../Views/edit.php';
        }
    }

    /**
     * Delete a section
     */
    private function delete(): void
    {
        $section = $_GET['section'] ?? '';

        try {
            $this->model->delete($section);
        } catch (\Exception $e) {
            die("Error deleting section: " . $e->getMessage());
        }

        header('Location: ?action=list');
    }
}
