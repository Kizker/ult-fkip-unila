import json

with open("adaptive_omr_results.json", "r") as f:
    data = json.load(f)

conclusion_counts = {
    "Sangat Praktis": 0,
    "Praktis": 0,
    "Cukup Praktis": 0,
    "Kurang Praktis": 0,
    "Tidak Praktis": 0
}

conclusion_map = {
    "a": "Sangat Praktis",
    "b": "Praktis",
    "c": "Cukup Praktis",
    "d": "Kurang Praktis",
    "e": "Tidak Praktis"
}

for k, v in sorted(data.items()):
    conclusion_code = v["conclusion"]
    new_cat = conclusion_map.get(conclusion_code, "N/A")
    conclusion_counts[new_cat] += 1

print("\nNew Practicality Category Distribution:")
for cat, count in conclusion_counts.items():
    print(f"  - {cat}: {count} Responden")
