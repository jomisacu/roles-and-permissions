alter table {{actor_role_relations_table}}
    add column updated_at timestamp(0) without time zone null;

create unique index {{actor_permission_relations_unique_rule_index}}
    on {{actor_permission_relations_table}} (context_id, actor_id, permission_id, negated, resource);

create unique index {{actor_role_relations_unique_assignment_index}}
    on {{actor_role_relations_table}} (context_id, role_id, actor_id);

create unique index {{role_permission_relations_unique_rule_index}}
    on {{role_permission_relations_table}} (context_id, role_id, permission_id, negated, resource);
