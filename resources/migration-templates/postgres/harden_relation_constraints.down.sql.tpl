drop index {{role_permission_relations_unique_rule_index}};

drop index {{actor_role_relations_unique_assignment_index}};

drop index {{actor_permission_relations_unique_rule_index}};

alter table {{actor_role_relations_table}}
    drop column updated_at;
