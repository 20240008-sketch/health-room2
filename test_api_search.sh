#!/bin/bash

echo "🌐 API経由での検索テスト"
echo "======================="
echo ""

BASE_URL="http://localhost:8000/api/v1/attendance-records"

echo "📌 テスト1: 名前検索 (桑原)"
echo "---"
curl -s "${BASE_URL}?search=桑原" | python3 -m json.tool | head -20
echo ""
echo ""

echo "📌 テスト2: かな検索 (たし)"
echo "---"
curl -s "${BASE_URL}?search=たし" | python3 -m json.tool | head -20
echo ""
echo ""

echo "📌 テスト3: 出席番号検索 (5)"
echo "---"
curl -s "${BASE_URL}?search=5" | python3 -m json.tool | head -20
echo ""
echo ""

echo "📌 テスト4: 日付 + 検索"
echo "---"
curl -s "${BASE_URL}?date=2025-10-20&search=田" | python3 -m json.tool | head -20
echo ""

echo "✅ APIテスト完了"
