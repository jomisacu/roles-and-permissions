drop index {{role_permission_relations_unique_rule_index}} on {{role_permission_relations_table}};

drop index {{actor_role_relations_unique_assignment_index}} on {{actor_role_relations_table}};

drop index {{actor_permission_relations_unique_rule_index}} on {{actor_permission_relations_table}};

alter table {{actor_role_relations_table}}
    drop column updated_at;
