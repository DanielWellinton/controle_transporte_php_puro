<?php

if (!function_exists('has_role')) {
    function has_role(string|array $roles): bool {
        $userRoles = $_SESSION['user_roles'] ?? [];
        
        if (is_string($roles)) {
            return in_array($roles, $userRoles);
        }
        
        // Se passar um array de papéis, verifica se tem pelo menos um deles
        return !empty(array_intersect($roles, $userRoles));
    }
}